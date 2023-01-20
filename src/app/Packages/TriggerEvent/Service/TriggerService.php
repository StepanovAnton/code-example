<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Service;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\ActionTemplates\AbstractAction;
use App\Packages\TriggerEvent\ActionTemplates\Interfaces\ActionInterface;
use App\Packages\TriggerEvent\Dictionary\EventStatuses;
use App\Packages\TriggerEvent\Exception\EventStatusNotFind;
use App\Packages\TriggerEvent\Exception\UnknownActionException;
use App\Packages\TriggerEvent\Exception\UnknownRuleException;
use App\Packages\TriggerEvent\Factories\TriggerObjectsFactory;
use App\Packages\TriggerEvent\Jobs\ProcessTriggerJob;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\Repository\TriggerRepository;
use App\Packages\TriggerEvent\RuleTemplates\AbstractRule;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use ReflectionException;

class TriggerService
{
    private EventRepository $eventRepository;
    private TriggerRepository $triggerRepository;
    private TriggerObjectsFactory $triggerObjectFactory;

    public function __construct(
        EventRepository $eventRepository,
        TriggerRepository $triggerRepository,
        TriggerObjectsFactory $triggerObjectsFactory,
    ) {
        $this->eventRepository = $eventRepository;
        $this->triggerRepository = $triggerRepository;
        $this->triggerObjectFactory = $triggerObjectsFactory;
    }

    /**
     * @throws EventStatusNotFind
     */
    public function processEvent(Event $event)
    {
        $this->eventRepository->changeStatus($event, EventStatuses::CHECKED);
        $triggers = $this->triggerRepository->getActiveTriggersByEventName($event->name);

        if ($triggers->isEmpty()) {
            $this->eventRepository->changeStatus($event, EventStatuses::SKIPPED);

            return;
        }

        foreach ($triggers as $trigger) {
            $job = new ProcessTriggerJob($event, $trigger);
            $job->onQueue('triggerQueue');
            dispatch($job);
        }
    }

    /**
     * @throws BindingResolutionException
     * @throws UnknownRuleException
     * @throws ReflectionException
     */
    public function processTriggerRules(Trigger $trigger, Event $event): bool
    {
        $rulesObjects = $this->triggerObjectFactory->getRulesObjectsWithSort($trigger->rules);
        $arrayOfPayloads = $this->payloadsToArray($event->payloads->toArray());

        /** @var RuleInterface $rulesObject */
        foreach ($rulesObjects as $rulesObject) {
            if ($rulesObject instanceof AbstractRule) {
                $rulesObject->setTrigger($trigger);
                $rulesObject->assign(array_merge($arrayOfPayloads, $rulesObject->getRuleVariables()));
            }

            if (!$rulesObject->processRule($event, $trigger)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws BindingResolutionException
     * @throws UnknownActionException
     * @throws ReflectionException
     */
    public function processTriggerActions(Trigger $trigger, Event $event): bool
    {
        $actionObjects = $this->triggerObjectFactory->getActionObjectsWithSort($trigger->actions);
        $arrayOfPayloads = $this->payloadsToArray($event->payloads->toArray());

        /** @var ActionInterface $actionsObject */
        foreach ($actionObjects as $actionsObject) {
            if ($actionsObject instanceof AbstractAction) {
                $actionsObject->setTrigger($trigger);
                $actionsObject->assign(array_merge($arrayOfPayloads, $actionsObject->getActionVariables()));
            }

            if (!$actionsObject->processAction($event, $trigger)) {
                return false;
            }
        }

        return true;
    }

    private function payloadsToArray(array $payloads): array
    {
        $arrayOfProperties = [];

        foreach ($payloads as $payload) {
            $arrayOfProperties[$payload['object_type']] = $payload['object_value'];
        }

        return $arrayOfProperties;
    }
}
