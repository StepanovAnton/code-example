<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\ActionTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Models\TriggerLog;
use App\Packages\Amplitude\AmplitudeEventSender;
use App\Packages\Amplitude\Dictionary\GeneralEventKeys;
use App\Packages\Amplitude\Factories\EventFactory;
use App\Packages\PushLogger\Dto\LoggablePushDto;
use App\Packages\TriggerEvent\ActionTemplates\Interfaces\ActionInterface;
use App\Packages\TriggerEvent\Contract\Interfaces\UserRepositoryInterface;
use App\Packages\TriggerEvent\Dto\CreateTriggerLogDTO;
use App\Packages\TriggerEvent\Dto\GetAndGeneratePromoCodeRequest;
use App\Packages\TriggerEvent\Dto\SenderDTO;
use App\Packages\TriggerEvent\Dto\SendPushResponseDTO;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\Repository\TriggerLogRepository;
use App\Packages\TriggerEvent\Service\GetAndGeneratePromoCodeService;
use App\Packages\TriggerEvent\Service\SenderServiceFacade;
use AssGuard\Contracts\v1\SendRequest\MessageType;

class UserCommunicationActionTemplate extends AbstractAction implements ActionInterface
{
    private EventRepository $eventRepository;
    private TriggerLogRepository $triggerLogRepository;
    private UserRepositoryInterface $userRepository;
    private SenderServiceFacade $senderServiceFacade;
    private GetAndGeneratePromoCodeService $getAndGeneratePromoCodeService;
    private AmplitudeEventSender $amplitudeSender;
    private EventFactory $eventFactory;

    public string $title;
    public string $body;
    public bool $sendSms;
    public bool $sendPush;
    public bool $sendPrivateMessage;
    public ?string $pushLink = null;
    public ?string $image = null;
    public ?string $actionCode = null;
    public ?string $clientType = null;
    public ?string $pushType = null;
    public ?string $campaignId = null;

    public ?string $promoCode = null;
    public ?string $masterOrderId = null;
    public ?string $paymentUrl = null;

    public int $userId = 0;
    public ?string $orderId = null;
    public ?array $data = [];

    private const VLADIMIR_ZAKOTNEV_EXTERNAL_ID = 95001499;

    public function __construct(
        EventRepository $eventRepository,
        TriggerLogRepository $triggerLogRepository,
        UserRepositoryInterface $userRepository,
        SenderServiceFacade $senderServiceFacade,
        GetAndGeneratePromoCodeService $getAndGeneratePromoCodeService,
        AmplitudeEventSender $amplitudeSender,
        EventFactory $eventFactory,
    ) {
        $this->eventRepository = $eventRepository;
        $this->triggerLogRepository = $triggerLogRepository;
        $this->userRepository = $userRepository;
        $this->senderServiceFacade = $senderServiceFacade;
        $this->getAndGeneratePromoCodeService = $getAndGeneratePromoCodeService;
        $this->amplitudeSender = $amplitudeSender;
        $this->eventFactory = $eventFactory;
    }

    private function getAndGeneratePromoCode(): ?string
    {
        $getAndGeneratePromoCodeRequest = new GetAndGeneratePromoCodeRequest($this->actionCode, $this->userId);

        $result = $this->getAndGeneratePromoCodeService->process($getAndGeneratePromoCodeRequest);

        if ($result->getResult()) {
            return $result->getPromoCode();
        }

        return null;
    }

    private function fillTemplateVariables()
    {
        $this->title = $this->fillTemplate(['paymentUrl' => $this->paymentUrl, 'masterOrderId' => $this->masterOrderId, 'promocode' => $this->promoCode, 'userId' => $this->userId, 'orderId' => $this->orderId], $this->title);
        $this->body = $this->fillTemplate(['paymentUrl' => $this->paymentUrl, 'masterOrderId' => $this->masterOrderId, 'promocode' => $this->promoCode, 'userId' => $this->userId, 'orderId' => $this->orderId], $this->body);
        $this->pushLink = $this->fillTemplate(['paymentUrl' => $this->paymentUrl, 'masterOrderId' => $this->masterOrderId, 'promocode' => $this->promoCode, 'userId' => $this->userId, 'orderId' => $this->orderId], $this->pushLink);
    }

    private function generateLoggablePushDto(): LoggablePushDto
    {
        $loggablePushDto = (new LoggablePushDto())
            ->setClientType($this->clientType ?: 'buyer')
            ->setSource(GeneralEventKeys::TRIGGER_SOURCE)
            ->setType($this->pushType ?: 'marketing')
            ->setUserId($this->userId)
            ->setMessageId(null)
            ->setText($this->body)
            ->setImage(false)
            ->setPromoId($this->trigger->id)
            ->setIsSendPrivateMessage(false)
            ->setIsSendPush(false)
            ->setIsSendSms(false)
            ->setTitle($this->title)
            ->setPushClass(GeneralEventKeys::TRIGGER_PUSH_CLASS)
            ->setCampaignId($this->campaignId);

        if (isset($this->image) && $this->image) {
            $loggablePushDto->setImage(true);
        }

        if (isset($this->promoCode) && $this->promoCode) {
            $loggablePushDto->setPromoCode($this->promoCode);
        }

        if (isset($this->pushLink) && $this->pushLink) {
            $loggablePushDto->setLink($this->pushLink);
        }

        return $loggablePushDto;
    }

    public function processAction(Event $event, Trigger $trigger): bool
    {
        if ($this->userId === 0) {
            $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().'. Не указан userId');

            return false;
        }

        if (!$this->userRepository->isUserExistByExternalId($this->userId)) {
            $this->logMessage($event, "Пользователь с external_id $this->userId не найден");

            return false;
        }

        if ($this->image) {
            $this->data['imageUrl'] = $this->image;
        }

        if (!empty($this->actionCode)) {
            try {
                $this->promoCode = $this->getAndGeneratePromoCode();
            } catch (\Exception $exception) {
                $this->logMessage($event, $exception->getMessage());

                return false;
            }
        }

        $this->fillTemplateVariables();

        $loggablePushDTO = ($this->generateLoggablePushDto());

        if ($this->sendPrivateMessage === true) {
            if ($this->sendPrivateMessage()) {
                $loggablePushDTO->setIsSendPrivateMessage(true);
            }
        }

        if ($this->sendPush === true) {
            $responseDTO = $this->sendPush($trigger);

            if ($responseDTO->getIsSent() === TriggerLog::IS_SENT) {
                $loggablePushDTO->setIsSendPush(true);
            }
        }

        if ($this->sendSms === true) {
            if ($this->sendSms()) {
                $loggablePushDTO->setIsSendSms(true);
            }
        }

        // Отправка события в амплитуду
        if ($loggablePushDTO->getIsSendPush() === true || $loggablePushDTO->getIsSendPrivateMessage() === true || $loggablePushDTO->getIsSendSms() === true) {
            $amplitudeEvent = $this->eventFactory->createEvent($loggablePushDTO);

            if ($amplitudeEvent) {
                $this->amplitudeSender->send($amplitudeEvent);
            }
        }

        return true;
    }

    private function createTriggerLog(Trigger $trigger, string $reason, int $type, int $isSent)
    {
        $createTriggerLogDto = new CreateTriggerLogDTO(
            $trigger->id,
            $this->promoCode,
            $this->userId,
            $type,
            $reason,
            $this->action->id,
            self::getName(),
            $isSent
        );

        $this->triggerLogRepository->create($createTriggerLogDto);
    }

    private function fillTemplate(array $variables, string $template): string
    {
        foreach ($variables as $key => $val) {
            $template = str_replace("{{ $key }}", (string)$val, $template);
        }

        return $template;
    }

    private function logMessage(Event $event, string $message)
    {
        $this->eventRepository
            ->setLogMessage($event, 'Действие: '.self::getName().'. '.$message);
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'title' => [
                'design' => 'Input',
                'type' => 'text',
                'required' => true,
                'title' => 'Заголовок',
                'placeholder' => '',
            ],
            'body' => [
                'design' => 'Input',
                'type' => 'text',
                'required' => true,
                'title' => 'Сообщение',
                'placeholder' => '',
            ],
            'sendSms' => [
                'design' => 'Checkbox',
                'title' => 'Отправить смс',
                'placeholder' => '',
                'required' => false,
            ],
            'sendPush' => [
                'design' => 'Checkbox',
                'title' => 'Отправить пуш',
                'placeholder' => '',
                'required' => false,
            ],
            'pushLink' => [
                'design' => 'Input',
                'type' => 'text',
                'title' => 'Ссылка куда ведет пуш',
                'placeholder' => '',
                'required' => false,
            ],
            'image' => [
                'design' => 'Input',
                'type' => 'file',
                'required' => false,
                'title' => 'Изображение для пуша',
            ],
            'sendPrivateMessage' => [
                'design' => 'Checkbox',
                'title' => 'Отправить сообщение в Личный кабинет',
                'placeholder' => '',
                'required' => false,
            ],
            'actionCode' => [
                'design' => 'Input',
                'type' => 'text',
                'required' => false,
                'title' => 'Код акции',
                'placeholder' => '',
            ],
            'pushType' => [
                'design' => 'Input',
                'type' => 'text',
                'required' => true,
                'title' => 'Тип пуша',
                'placeholder' => 'Тип пуша',
            ],
            'clientType' => [
                'design' => 'Select',
                'required' => true,
                'title' => 'Тип клиента',
                'placeholder' => 'Тип клиента',
                'options' => ['buyer' => 'buyer', 'new' => 'new'],
            ],
            'campaignId' => [
                'design' => 'Input',
                'type' => 'text',
                'required' => true,
                'title' => 'Наименование компании',
                'placeholder' => 'Наименование компании',
            ],
        ];
    }

    public static function getName(): string
    {
        return 'user_communication_action_template';
    }

    public static function getLabel(): string
    {
        return 'Коммуникация с пользователем';
    }

    private function sendSms(): bool
    {
        $phone = $this->userRepository->getPhoneByKpp($this->userId);

        $senderDTO = (new SenderDTO())
            ->setIdentifier((string)$phone)
            ->setCreatorId(self::VLADIMIR_ZAKOTNEV_EXTERNAL_ID)
            ->setBody($this->body);

        $response = $this->senderServiceFacade->sendSMS($senderDTO);

        $isSent = $response->getStatus() ? TriggerLog::IS_SENT : TriggerLog::IS_NOT_SENT;
        $this->createTriggerLog($this->trigger, $response->getReason(), TriggerLog::TYPE_SMS, $isSent);

        return (bool)$isSent;
    }

    private function sendPush(Trigger $trigger): SendPushResponseDTO
    {
        $tokens = $this->userRepository->getTokensByBuyerId($this->userId);

        $responseDTO = (new SendPushResponseDTO())->setIsSent(TriggerLog::IS_NOT_SENT);
        $isSent = TriggerLog::IS_NOT_SENT;

        $data = array_merge([
            'push_type' => (string)$this->pushType ?: 'marketing',
            'source' => GeneralEventKeys::TRIGGER_SOURCE,
            'client_type' => (string)$this->clientType ?: 'buyer',
            'push_link' => (string)$this->pushLink ?: 'null',
            'SMS' => $this->sendSms ? 'true' : 'false',
            'Push' => $this->sendPush ? 'true' : 'false',
            'Admin' => $this->sendPrivateMessage ? 'true' : 'false',
            'push_coupon_code' => (string)$this->promoCode ?: 'null',
            'title' => $this->title,
            'Push_class' => GeneralEventKeys::TRIGGER_PUSH_CLASS,
            'image' => $this->image ? 'true' : 'false',
            'campaign_id' => $this->campaignId ?: 'null',
            'push_promo_id' => (string)$this->trigger->id,
        ], $this->data);

        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                $senderDTO = (new SenderDTO())
                    ->setIdentifier($token['token'])
                    ->setTitle($this->title)
                    ->setBody($this->body)
                    ->setLink($this->pushLink)
                    ->setData($data)
                    ->setAppends($this->sendPrivateMessage ? ['content-available' => true] : null)
                    ->setPlatform($token['platform'])
                    ->setVersion($token['version']);

                $response = $this->senderServiceFacade->sendFirebase($senderDTO);

                if ($response->getStatus() === true && $isSent === TriggerLog::IS_NOT_SENT) {
                    $isSent = TriggerLog::IS_SENT;
                    $this->createTriggerLog($trigger, 'Отправка на токен - '.$token['token'], TriggerLog::TYPE_PUSH, $isSent);
                    $responseDTO->setIsSent(TriggerLog::IS_SENT)->setPushId($response->getId())->setToken($token['token']);
                }
            }

            if ($isSent === TriggerLog::IS_NOT_SENT) {
                $this->createTriggerLog($trigger, 'Не найден рабочий токен для отправки пуша', TriggerLog::TYPE_PUSH, $isSent);
            }
        } else {
            $this->createTriggerLog($trigger, "Не найден пуш токен пользователя $this->userId", TriggerLog::TYPE_PUSH, TriggerLog::IS_NOT_SENT);
        }

        return $responseDTO;
    }

    private function sendPrivateMessage(): bool
    {
        $senderDTO = (new SenderDTO())
            ->setIdentifier((string)$this->userId)
            ->setTitle($this->title)
            ->setCreatorId(self::VLADIMIR_ZAKOTNEV_EXTERNAL_ID)
            ->setBody($this->body)
            ->setType(MessageType::TRIGGER);

        if ($this->promoCode) {
            $senderDTO->setPromoCode($this->promoCode);
        }

        $response = $this->senderServiceFacade->sendPrivateMessage($senderDTO);

        $isSent = $response->getStatus() ? TriggerLog::IS_SENT : TriggerLog::IS_NOT_SENT;
        $this->createTriggerLog($this->trigger, $response->getReason(), TriggerLog::TYPE_PRIVATE_MESSAGE, $isSent);

        return (bool)$isSent;
    }
}
