<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Packages\TriggerEvent\Dto\RuleTemplateDto;
use App\Packages\TriggerEvent\Exception\UnknownRuleException;
use App\Packages\TriggerEvent\RuleTemplates\AbstractRule;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;
use AssGuard\Contracts\v1\RuleTemplate;
use Illuminate\Contracts\Container\BindingResolutionException;

class RuleTemplateRepository
{
    /**
     * @throws UnknownRuleException|BindingResolutionException
     */
    public function findByName(string $name): RuleTemplateDto
    {
        $ruleTemplatesList = config('triggers.ruleTemplates');

        if (!isset($ruleTemplatesList[$name])) {
            throw new UnknownRuleException();
        }

        $rule = $ruleTemplatesList[$name];

        /** @var RuleInterface $ruleObject */
        $ruleObject = app()->make($rule);

        $variables = [];

        if ($ruleObject instanceof AbstractRule) {
            $variables = $ruleObject->getFieldsWithDefaults();
        }

        return new RuleTemplateDto(
            $ruleObject::getName(),
            $ruleObject::getLabel(),
            $rule,
            $variables,
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function getList(): array
    {
        $ruleTemplatesList = config('triggers.ruleTemplates');
        $result = [];

        foreach ($ruleTemplatesList as $ruleTemplate) {
            /** @var RuleInterface $ruleObject */
            $ruleObject = app()->make($ruleTemplate);

            $variables = [];

            if ($ruleObject instanceof AbstractRule) {
                $variables = $ruleObject->getFieldsWithDefaults();
            }

            $result[] = new RuleTemplateDto(
                $ruleObject::getName(),
                $ruleObject::getLabel(),
                $ruleTemplate,
                $variables
            );
        }

        return $result;
    }

    public function convertDtoToGrpcMessage(RuleTemplateDto $ruleTemplateDto): RuleTemplate
    {
        return (new RuleTemplate())
            ->setName($ruleTemplateDto->getName())
            ->setLabel($ruleTemplateDto->getLabel())
            ->setVariables(json_encode($ruleTemplateDto->getVariables()));
    }
}
