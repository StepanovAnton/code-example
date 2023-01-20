<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\ActionTemplates;

use App\Infrastructure\Notification\Db\Repository\UserRepository;
use App\Models\Event;
use App\Models\Payload;
use App\Models\Trigger;
use App\Models\TriggerLog;
use App\Packages\Notification\Grpc\SendPushServiceGrpc;
use App\Packages\TriggerEvent\ActionTemplates\Interfaces\ActionInterface;
use App\Packages\TriggerEvent\Dto\CreateTriggerLogDTO;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\Repository\TriggerLogRepository;
use AssGuard\Contracts\v1\SendFirstFreeDeliveryRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spiral\RoadRunner\GRPC\Context;

/**
 * @deprecated
 */
class SendFreeDeliveryPushNotificationActionTemplate extends AbstractAction implements ActionInterface
{
    private SendPushServiceGrpc $sendPushServiceGrpc;
    private TriggerLogRepository $triggerLogRepository;
    private EventRepository $eventRepository;
    private UserRepository $userRepository;

    public function __construct(
        SendPushServiceGrpc $sendPushServiceGrpc,
        TriggerLogRepository $triggerLogRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository
    ) {
        $this->sendPushServiceGrpc = $sendPushServiceGrpc;
        $this->triggerLogRepository = $triggerLogRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws BindingResolutionException
     */
    public function processAction(Event $event, Trigger $trigger): bool
    {
        $userId = 0;
        $payloads = $event->payloads;

        /** @var Payload $payload */
        foreach ($payloads as $payload) {
            if ($payload->object_type === 'userId') {
                $userId = (int)$payload->object_value;
            }
        }

        if ($userId === 0) {
            $this->eventRepository->setLogMessage($event, 'Действие: Send_free_delivery_push_action. Не указан пользователь userId');

            return false;
        }

        if (!$this->checkUserCreatedInRange($userId)) {
            $this->eventRepository->setLogMessage($event, 'Действие: Send_free_delivery_push_action. Пользователь не попадает под дату регистрации');

            return false;
        }

        $response = $this->sendPushServiceGrpc->sendFirstFreeDelivery(new Context([]), (new SendFirstFreeDeliveryRequest())->setUserId($userId)->setEventId($event->id));

        if (!$response->getResult()) {
            $this->eventRepository
                ->setLogMessage($event, 'Действие: Send_free_delivery_push_action. Не удалось отправить пуш');

            return false;
        }

        $triggerLogDTO = new CreateTriggerLogDTO($trigger->id, '', $userId, $this->triggerLogRepository::TYPE_PUSH, $response->getMessage(), $this->action->id, self::getName(), TriggerLog::IS_SENT);
        $this->triggerLogRepository->create($triggerLogDTO);

        return true;
    }

    public function checkUserCreatedInRange(int $userId): bool
    {
        $user = $this->userRepository->getUserByExternalId($userId);

        if ($user) {
            $fromCreated = Carbon::createFromFormat('Y-m-d', $this->getFieldByName('userCreatedFrom'));
            $userCreated = Carbon::createFromFormat('Y-m-d 00:00:00', $user->created_at->format('Y-m-d 00:00:00'));

            return $userCreated->gte($fromCreated);
        }

        return false;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'userCreatedFrom' => [
                'design' => 'DateTimer',
                'required' => true,
                'title' => 'Начиная с какой даты регистрации пользователя присылать ему пуш',
                'placeholder' => '',
                'format' => 'Y-m-d',
            ],
        ];
    }

    public static function getName(): string
    {
        return 'send_free_delivery_push_action';
    }

    public static function getLabel(): string
    {
        return 'Пуш уведомление о бесплатной первой доставке';
    }
}
