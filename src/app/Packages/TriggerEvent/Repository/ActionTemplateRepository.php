<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Repository;

use App\Packages\TriggerEvent\ActionTemplates\AbstractAction;
use App\Packages\TriggerEvent\ActionTemplates\Interfaces\ActionInterface;
use App\Packages\TriggerEvent\Dto\ActionTemplateDto;
use App\Packages\TriggerEvent\Exception\UnknownActionException;
use AssGuard\Contracts\v1\ActionTemplate;
use Illuminate\Contracts\Container\BindingResolutionException;

class ActionTemplateRepository
{
    /**
     * @throws UnknownActionException|BindingResolutionException
     */
    public function findByName(string $name): ActionTemplateDto
    {
        $actionTemplatesList = config('triggers.actionTemplates');

        if (!isset($actionTemplatesList[$name])) {
            throw new UnknownActionException();
        }

        $actionTemplate = $actionTemplatesList[$name];

        /** @var ActionInterface $actionObject */
        $actionObject = app()->make($actionTemplate);

        $variables = [];

        if ($actionObject instanceof AbstractAction) {
            $variables = $actionObject->getFieldsWithDefaults();
        }

        return new ActionTemplateDto(
            $actionObject->getName(),
            $actionObject->getLabel(),
            $actionTemplate,
            $variables
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function getList(): array
    {
        $actionTemplatesList = config('triggers.actionTemplates');
        $result = [];

        foreach ($actionTemplatesList as $actionTemplate) {
            /** @var ActionInterface $actionObject */
            $actionObject = app()->make($actionTemplate);

            $variables = [];

            if ($actionObject instanceof AbstractAction) {
                $variables = $actionObject->getFieldsWithDefaults();
            }

            $result[] = new ActionTemplateDto(
                $actionObject->getName(),
                $actionObject->getLabel(),
                $actionTemplate,
                $variables
            );
        }

        return $result;
    }

    public function convertDtoToGrpcMessage(ActionTemplateDto $actionTemplateDto): ActionTemplate
    {
        return (new ActionTemplate())
            ->setName($actionTemplateDto->getName())
            ->setLabel($actionTemplateDto->getLabel())
            ->setVariables(json_encode($actionTemplateDto->getVariables()));
    }
}
