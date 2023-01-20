<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Grpc;

use App\Models\Action;
use App\Models\Rule;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Dto\ActionTemplateDto;
use App\Packages\TriggerEvent\Dto\CreateActionDTO;
use App\Packages\TriggerEvent\Dto\CreateRuleDTO;
use App\Packages\TriggerEvent\Dto\CreateTriggerDTO;
use App\Packages\TriggerEvent\Dto\RuleTemplateDto;
use App\Packages\TriggerEvent\Dto\UpdateActionDTO;
use App\Packages\TriggerEvent\Dto\UpdateRuleDTO;
use App\Packages\TriggerEvent\Dto\UpdateTriggerDTO;
use App\Packages\TriggerEvent\Exception\UnknownActionException;
use App\Packages\TriggerEvent\Exception\UnknownRuleException;
use App\Packages\TriggerEvent\Repository\ActionRepository;
use App\Packages\TriggerEvent\Repository\ActionTemplateRepository;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\Repository\PayloadRepository;
use App\Packages\TriggerEvent\Repository\RuleRepository;
use App\Packages\TriggerEvent\Repository\RuleTemplateRepository;
use App\Packages\TriggerEvent\Repository\TriggerRepository;
use App\Packages\TriggerEvent\Service\GetAndGeneratePromoCodeService;
use AssGuard\Contracts\v1\CreateActionRequest;
use AssGuard\Contracts\v1\CreateActionResponse;
use AssGuard\Contracts\v1\CreateRuleRequest;
use AssGuard\Contracts\v1\CreateRuleResponse;
use AssGuard\Contracts\v1\CreateTriggerRequest;
use AssGuard\Contracts\v1\CreateTriggerResponse;
use AssGuard\Contracts\v1\DeleteActionRequest;
use AssGuard\Contracts\v1\DeleteActionResponse;
use AssGuard\Contracts\v1\DeleteRuleRequest;
use AssGuard\Contracts\v1\DeleteRuleResponse;
use AssGuard\Contracts\v1\DeleteTriggerRequest;
use AssGuard\Contracts\v1\DeleteTriggerResponse;
use AssGuard\Contracts\v1\GetActionRequest;
use AssGuard\Contracts\v1\GetActionResponse;
use AssGuard\Contracts\v1\GetActionsListResponse;
use AssGuard\Contracts\v1\GetActionsTemplateListResponse;
use AssGuard\Contracts\v1\GetActionTemplateRequest;
use AssGuard\Contracts\v1\GetActionTemplateResponse;
use AssGuard\Contracts\v1\GetRuleRequest;
use AssGuard\Contracts\v1\GetRuleResponse;
use AssGuard\Contracts\v1\GetRulesListResponse;
use AssGuard\Contracts\v1\GetRulesTemplateListResponse;
use AssGuard\Contracts\v1\GetRuleTemplateRequest;
use AssGuard\Contracts\v1\GetRuleTemplateResponse;
use AssGuard\Contracts\v1\GetTriggerRequest;
use AssGuard\Contracts\v1\GetTriggerResponse;
use AssGuard\Contracts\v1\GetTriggersListResponse;
use AssGuard\Contracts\v1\Payload;
use AssGuard\Contracts\v1\SendEventRequest;
use AssGuard\Contracts\v1\SendEventResponse;
use AssGuard\Contracts\v1\TriggerEventServiceInterface;
use AssGuard\Contracts\v1\UpdateActionRequest;
use AssGuard\Contracts\v1\UpdateActionResponse;
use AssGuard\Contracts\v1\UpdateRuleRequest;
use AssGuard\Contracts\v1\UpdateRuleResponse;
use AssGuard\Contracts\v1\UpdateTriggerRequest;
use AssGuard\Contracts\v1\UpdateTriggerResponse;
use AssGuard\Contracts\v1\Variable;
use Google\Protobuf;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spiral\RoadRunner\GRPC\ContextInterface;

class TriggerEventServiceGrpc implements TriggerEventServiceInterface
{
    private PayloadRepository $payloadRepository;
    private EventRepository $eventRepository;
    private TriggerRepository $triggerRepository;
    private RuleRepository $ruleRepository;
    private ActionRepository $actionRepository;
    private RuleTemplateRepository $ruleTemplateRepository;
    private ActionTemplateRepository $actionTemplateRepository;
    private GetAndGeneratePromoCodeService $getAndGeneratePromoCodeService;

    public function __construct(
        PayloadRepository $payloadRepository,
        EventRepository $eventRepository,
        TriggerRepository $triggerRepository,
        RuleRepository $ruleRepository,
        ActionRepository $actionRepository,
        RuleTemplateRepository $ruleTemplateRepository,
        ActionTemplateRepository $actionTemplateRepository,
        GetAndGeneratePromoCodeService $getAndGeneratePromoCodeService
    ) {
        $this->payloadRepository = $payloadRepository;
        $this->eventRepository = $eventRepository;
        $this->triggerRepository = $triggerRepository;
        $this->ruleRepository = $ruleRepository;
        $this->actionRepository = $actionRepository;
        $this->ruleTemplateRepository = $ruleTemplateRepository;
        $this->actionTemplateRepository = $actionTemplateRepository;
        $this->getAndGeneratePromoCodeService = $getAndGeneratePromoCodeService;
    }

    public function sendEvent(ContextInterface $ctx, SendEventRequest $in): SendEventResponse
    {
        $event = $this->eventRepository->create($in->getName());

        /** @var Payload $payload */
        foreach ($in->getPayloads() as $payload) {
            $this->payloadRepository->create($event->id, $payload->getObjectType(), $payload->getObjectValue());
        }

        return (new SendEventResponse())->setResult(true);
    }

    public function getTriggersList(ContextInterface $ctx, Protobuf\GPBEmpty $in): GetTriggersListResponse
    {
        $triggersList = $this->triggerRepository->getList();
        $grpcTriggers = [];

        /** @var Trigger $trigger */
        foreach ($triggersList as $trigger) {
            $grpcTriggers[] = $trigger->toGrpcMessage();
        }

        return (new GetTriggersListResponse())->setTriggers($grpcTriggers);
    }

    public function getTrigger(ContextInterface $ctx, GetTriggerRequest $in): GetTriggerResponse
    {
        $trigger = $this->triggerRepository->getById($in->getId());

        return (new GetTriggerResponse())->setTrigger($trigger->toGrpcMessage());
    }

    public function createTrigger(ContextInterface $ctx, CreateTriggerRequest $in): CreateTriggerResponse
    {
        $actionsId = [];
        $rulesId = [];

        foreach ($in->getActionIds() as $actionId) {
            $actionsId[] = $actionId;
        }

        foreach ($in->getRuleIds() as $ruleId) {
            $rulesId[] = $ruleId;
        }

        $createdTriggerDTO = new CreateTriggerDTO($in->getTitle(), $in->getEventName(), $actionsId, $rulesId, $in->getEnabled());
        $trigger = $this->triggerRepository->create($createdTriggerDTO);

        if ($trigger) {
            return (new CreateTriggerResponse())->setId($trigger->id)->setResult(true);
        }

        return (new CreateTriggerResponse())->setResult(false)->setMessage('Ошибка при создании');
    }

    public function updateTrigger(ContextInterface $ctx, UpdateTriggerRequest $in): UpdateTriggerResponse
    {
        $actionsId = [];
        $rulesId = [];

        foreach ($in->getActionIds() as $actionId) {
            $actionsId[] = $actionId;
        }

        foreach ($in->getRuleIds() as $ruleId) {
            $rulesId[] = $ruleId;
        }

        $updateTriggerDTO = new UpdateTriggerDTO($in->getId(), $in->getTitle(), $in->getEventName(), $actionsId, $rulesId, $in->getEnabled());
        $trigger = $this->triggerRepository->update($updateTriggerDTO);

        if ($trigger) {
            return (new UpdateTriggerResponse())->setResult(true);
        }

        return (new UpdateTriggerResponse())->setResult(false)->setMessage('Ошибка при обновлении');
    }

    public function deleteTrigger(ContextInterface $ctx, DeleteTriggerRequest $in): DeleteTriggerResponse
    {
        $result = $this->triggerRepository->delete($in->getId());

        if ($result) {
            return (new DeleteTriggerResponse())->setResult(true);
        }

        return (new DeleteTriggerResponse())->setResult(false)->setMessage('Ошибка при удалении');
    }

    public function createRule(ContextInterface $ctx, CreateRuleRequest $in): CreateRuleResponse
    {
        $variables = [];

        /** @var Variable $variable */
        foreach ($in->getVariables() as $variable) {
            $variables[$variable->getKey()] = $variable->getValue();
        }

        $createRuleDTO = new CreateRuleDTO($in->getTitle(), $variables, $in->getTemplate());
        $result = $this->ruleRepository->create($createRuleDTO);

        if ($result) {
            return (new CreateRuleResponse())->setRule($result->toGrpcMessage())->setResult(true);
        }

        return (new CreateRuleResponse())->setMessage('Ошибка при создании')->setResult(false);
    }

    public function createAction(ContextInterface $ctx, CreateActionRequest $in): CreateActionResponse
    {
        $variables = [];

        /** @var Variable $variable */
        foreach ($in->getVariables() as $variable) {
            $variables[$variable->getKey()] = $variable->getValue();
        }

        if (isset($variables['actionId']) && !empty($variables['actionId'])) {
            if (!$this->getAndGeneratePromoCodeService->isExist($variables['actionId'])) {
                return (new CreateActionResponse())->setMessage('Не найдена промоакция: '.$variables['actionId'])->setResult(false);
            }
        }

        $createActionDTO = new CreateActionDTO($in->getTitle(), $variables, $in->getTemplate());
        $result = $this->actionRepository->create($createActionDTO);

        if ($result) {
            return (new CreateActionResponse())->setAction($result->toGrpcMessage())->setResult(true);
        }

        return (new CreateActionResponse())->setMessage('Ошибка при создании')->setResult(false);
    }

    public function updateRule(ContextInterface $ctx, UpdateRuleRequest $in): UpdateRuleResponse
    {
        $variables = [];

        /** @var Variable $variable */
        foreach ($in->getVariables() as $variable) {
            $variables[$variable->getKey()] = $variable->getValue();
        }

        $updateRuleDTO = new UpdateRuleDTO($in->getId(), $in->getTitle(), $variables, $in->getTemplate());
        $result = $this->ruleRepository->update($updateRuleDTO);

        if ($result) {
            return (new UpdateRuleResponse())->setRule($result->toGrpcMessage())->setResult(true);
        }

        return (new UpdateRuleResponse())->setMessage('Ошибка при обновлении')->setResult(false);
    }

    public function updateAction(ContextInterface $ctx, UpdateActionRequest $in): UpdateActionResponse
    {
        $variables = [];

        /** @var Variable $variable */
        foreach ($in->getVariables() as $variable) {
            $variables[$variable->getKey()] = $variable->getValue();
        }

        // Проверка на существование акции
        if (isset($variables['actionId']) && !empty($variables['actionId'])) {
            if (!$this->getAndGeneratePromoCodeService->isExist($variables['actionId'])) {
                return (new UpdateActionResponse())->setMessage('Не найдена промоакция: '.$variables['actionId'])->setResult(false);
            }
        }

        $updateActionDTO = new UpdateActionDTO($in->getId(), $in->getTitle(), $variables, $in->getTemplate());
        $result = $this->actionRepository->update($updateActionDTO);

        if ($result) {
            return (new UpdateActionResponse())->setAction($result->toGrpcMessage())->setResult(true);
        }

        return (new UpdateActionResponse())->setMessage('Ошибка при обновлении')->setResult(false);
    }

    public function getRulesList(ContextInterface $ctx, Protobuf\GPBEmpty $in): GetRulesListResponse
    {
        $rules = [];

        /** @var Rule $rule */
        foreach ($this->ruleRepository->getAll() as $rule) {
            $rules[] = $rule->toGrpcMessage();
        }

        return (new GetRulesListResponse())->setRules($rules);
    }

    public function getActionsList(ContextInterface $ctx, Protobuf\GPBEmpty $in): GetActionsListResponse
    {
        $actions = [];

        /** @var Action $action */
        foreach ($this->actionRepository->getAll() as $action) {
            $actions[] = $action->toGrpcMessage();
        }

        return (new GetActionsListResponse())->setActions($actions);
    }

    public function deleteRule(ContextInterface $ctx, DeleteRuleRequest $in): DeleteRuleResponse
    {
        $result = $this->ruleRepository->delete($in->getId());

        if ($result) {
            return (new DeleteRuleResponse())->setResult(true);
        }

        return (new DeleteRuleResponse())->setResult(false)->setMessage('Ошибка при удалении');
    }

    public function deleteAction(ContextInterface $ctx, DeleteActionRequest $in): DeleteActionResponse
    {
        $result = $this->actionRepository->delete($in->getId());

        if ($result) {
            return (new DeleteActionResponse())->setResult(true);
        }

        return (new DeleteActionResponse())->setResult(false)->setMessage('Ошибка при удалении');
    }

    public function getRule(ContextInterface $ctx, GetRuleRequest $in): GetRuleResponse
    {
        $rule = $this->ruleRepository->getById($in->getId());

        return (new GetRuleResponse())->setRule($rule->toGrpcMessage());
    }

    public function getAction(ContextInterface $ctx, GetActionRequest $in): GetActionResponse
    {
        $action = $this->actionRepository->getById($in->getId());

        return (new GetActionResponse())->setAction($action->toGrpcMessage());
    }

    public function getRulesTemplateList(ContextInterface $ctx, Protobuf\GPBEmpty $in): GetRulesTemplateListResponse
    {
        $ruleTemplates = [];

        /** @var RuleTemplateDto $dto */
        foreach ($this->ruleTemplateRepository->getList() as $dto) {
            $ruleTemplates[] = $this->ruleTemplateRepository->convertDtoToGrpcMessage($dto);
        }

        return (new GetRulesTemplateListResponse())->setRuleTemplates($ruleTemplates);
    }

    public function getActionsTemplateList(ContextInterface $ctx, Protobuf\GPBEmpty $in): GetActionsTemplateListResponse
    {
        $actionTemplates = [];

        /** @var ActionTemplateDto $dto */
        foreach ($this->actionTemplateRepository->getList() as $dto) {
            $actionTemplates[] = $this->actionTemplateRepository->convertDtoToGrpcMessage($dto);
        }

        return (new GetActionsTemplateListResponse())->setActionTemplates($actionTemplates);
    }

    /**
     * @throws BindingResolutionException
     * @throws UnknownRuleException
     */
    public function getRuleTemplate(ContextInterface $ctx, GetRuleTemplateRequest $in): GetRuleTemplateResponse
    {
        $dto = $this->ruleTemplateRepository->findByName($in->getName());
        $template = $this->ruleTemplateRepository->convertDtoToGrpcMessage($dto);

        return (new GetRuleTemplateResponse())->setTemplate($template);
    }

    /**
     * @throws BindingResolutionException
     * @throws UnknownActionException
     */
    public function getActionTemplate(ContextInterface $ctx, GetActionTemplateRequest $in): GetActionTemplateResponse
    {
        $dto = $this->actionTemplateRepository->findByName($in->getName());
        $template = $this->actionTemplateRepository->convertDtoToGrpcMessage($dto);

        return (new GetActionTemplateResponse())->setTemplate($template);
    }
}
