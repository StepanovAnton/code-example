<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\Amplitude\AmplitudeEventSender;
use App\Packages\Amplitude\Dictionary\GeneralEventKeys;
use App\Packages\Amplitude\Factories\EventFactory;
use App\Packages\PushLogger\Dto\LoggablePushDto;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;
use App\Packages\TriggerEvent\Service\GetAuditoryForABService;

class ABTestRule extends AbstractRule implements RuleInterface
{
    private EventRepository $eventRepository;
    private EventFactory $eventFactory;
    private AmplitudeEventSender $amplitudeSender;

    public int $userId = 0;
    public string $salt = '';
    public int $percentCoverage = 0;
    public string $title = '';
    public string $body = '';
    public bool $sendSms = false;
    public bool $sendPush = false;
    public bool $sendPrivateMessage = false;
    public ?string $pushLink = null;
    public ?string $image = null;
    public ?string $actionCode = null;
    public ?string $clientType = null;
    public ?string $pushType = null;
    public ?string $campaignId = null;

    public ?string $promoCode = null;
    public ?string $masterOrderId = null;
    public ?string $paymentUrl = null;

    public ?string $orderId = null;
    public ?array $data = [];

    private GetAuditoryForABService $auditoryForABService;

    public function __construct(
        EventRepository $eventRepository,
        AmplitudeEventSender $amplitudeSender,
        EventFactory $eventFactory,
        GetAuditoryForABService $auditoryForABService

    ) {
        $this->eventRepository = $eventRepository;
        $this->amplitudeSender = $amplitudeSender;
        $this->eventFactory = $eventFactory;
        $this->auditoryForABService = $auditoryForABService;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        $this->processTriggerActions($trigger);
        $this->setTrigger($trigger);

        if ($this->userId === 0) {
            $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().'. Не указан userId');

            return false;
        }

        $auditory = $this->auditoryForABService->calculateValue($this->userId, $this->salt);
        if ($auditory <= ($this->percentCoverage / 10)) {
            return true;
        }

        $this->eventRepository->setLogMessage($event, 'Правило: '.self::getName().'. Не попал в выборку');

        $this->amplitudeSender->send($this->eventFactory->createNotSentPushEvent($this->generateLoggablePushDto()));

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'salt' => [
                'design' => 'Input',
                'required' => true,
                'type' => 'string',
                'title' => 'Строковой идентификатор эксперимента',
                'placeholder' => 'Строковой идентификатор эксперимента',
            ],
            'percentCoverage' => [
                'design' => 'Input',
                'required' => true,
                'type' => 'number',
                'title' => 'Процент покрытия эксперимента',
                'placeholder' => 'Процент покрытия эксперимента',
            ],
        ];
    }

    public static function getName(): string
    {
        return 'A/B_rule';
    }

    public static function getLabel(): string
    {
        return 'A/B тестирование';
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

    private function processTriggerActions(Trigger $trigger)
    {
        foreach ($trigger->actions as $actionsObject) {
            $this->assign($actionsObject->variables);
        }
    }
}
