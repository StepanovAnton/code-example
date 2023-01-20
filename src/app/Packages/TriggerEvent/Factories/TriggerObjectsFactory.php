<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Factories;

use App\Models\Action;
use App\Models\Rule;
use App\Packages\TriggerEvent\ActionTemplates\AbstractAction;
use App\Packages\TriggerEvent\Exception\UnknownActionException;
use App\Packages\TriggerEvent\Exception\UnknownRuleException;
use App\Packages\TriggerEvent\Repository\ActionTemplateRepository;
use App\Packages\TriggerEvent\Repository\RuleTemplateRepository;
use App\Packages\TriggerEvent\RuleTemplates\AbstractRule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

class TriggerObjectsFactory
{
    public const RULE_TEMPLATES = [
        0 => 'OrderCountRule',
    ];

    public const ACTION_TEMPLATES = [
        0 => 'OrderCountRule',
    ];

    private RuleTemplateRepository $ruleTemplateRepository;
    private ActionTemplateRepository $actionTemplateRepository;

    public function __construct(RuleTemplateRepository $ruleTemplateRepository, ActionTemplateRepository $actionTemplateRepository)
    {
        $this->ruleTemplateRepository = $ruleTemplateRepository;
        $this->actionTemplateRepository = $actionTemplateRepository;
    }

    /**
     * @param Collection $rules
     * @return array
     * @throws BindingResolutionException
     * @throws UnknownRuleException
     */
    public function getRulesObjectsWithSort(Collection $rules): array
    {
        $result = [];

        /** @var Rule $rule */
        foreach ($rules as $rule) {
            $ruleTemplate = $rule->template;
            $ruleTemplateDTO = $this->ruleTemplateRepository->findByName($ruleTemplate);

            $object = app()->make($ruleTemplateDTO->getHandler());
            if ($object instanceof AbstractRule) {
                $object->setRule($rule);
                $result[] = $object;
            }
        }

        return $result;
    }

    /**
     * @throws BindingResolutionException
     * @throws UnknownActionException
     */
    public function getActionObjectsWithSort(Collection $actions): array
    {
        $result = [];

        /** @var Action $action */
        foreach ($actions as $action) {
            $actionTemplate = $action->template;
            $actionTemplateDTO = $this->actionTemplateRepository->findByName($actionTemplate);

            $object = app()->make($actionTemplateDTO->getHandler());
            if ($object instanceof AbstractAction) {
                $object->setAction($action);
                $result[] = $object;
            }
        }

        return $result;
    }
}
