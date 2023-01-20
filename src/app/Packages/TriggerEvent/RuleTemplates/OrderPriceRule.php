<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\RuleTemplates;

use App\Models\Event;
use App\Models\Trigger;
use App\Packages\TriggerEvent\Contract\Interfaces\OrderRepositoryInterface;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\RuleTemplates\Interfaces\RuleInterface;

class OrderPriceRule extends AbstractRule implements RuleInterface
{
    public const OPERATOR_GTE = '>=';
    public const OPERATOR_LTE = '<=';
    public const OPERATOR_GT = '>';
    public const OPERATOR_LT = '<';
    public const OPERATOR_EQ = '=';
    public const OPERATOR_NEQ = '!=';

    private OrderRepositoryInterface $ordersRepository;
    private EventRepository $eventRepository;

    public string $operator;
    public int $price;
    public string $orderId;

    public function __construct(
        OrderRepositoryInterface $ordersRepository,
        EventRepository $eventRepository
    ) {
        $this->ordersRepository = $ordersRepository;
        $this->eventRepository = $eventRepository;
    }

    public function processRule(Event $event, Trigger $trigger): bool
    {
        $price = $this->ordersRepository->getOrderPrice($this->orderId);

        switch ($this->operator) {
            case self::OPERATOR_EQ:
                $result = $price === $this->price;
                break;
            case self::OPERATOR_NEQ:
                $result = $price !== $this->price;
                break;
            case self::OPERATOR_LT:
                $result = $price > $this->price;
                break;
            case self::OPERATOR_GT:
                $result = $price < $this->price;
                break;
            case self::OPERATOR_LTE:
                $result = $price >= $this->price;
                break;
            case self::OPERATOR_GTE:
                $result = $price <= $this->price;
                break;
            default:
                $result = false;
        }

        if (!$result) {
            $this->eventRepository->setLogMessage(
                $event, 'Правило: '.self::getName().'. Стоимость: '.$price.'. Ожидаемая - '.$this->price.'. Со знаком '.$this->operator
            );
        }

        return $result;
    }

    public function getFieldsWithDefaults(): array
    {
        return [
            'price' => [
                'design' => 'Input',
                'required' => true,
                'type' => 'number',
                'title' => 'Стоимость',
                'placeholder' => '',
            ],
            'operator' => [
                'design' => 'Select',
                'required' => true,
                'title' => 'Знак',
                'placeholder' => 'Знак',
                'options' => [
                    '=' => 'Х Равно Стоимости',
                    '>' => 'X Меньше Стоимости',
                    '<' => 'Х Больше Стоимости',
                    '>=' => 'Х Меньше равно Стоимости',
                    '<=' => 'Х Больше равно Стоимости',
                    '!=' => 'Х Не равен Стоимости',
                ],
            ],
        ];
    }

    public static function getName(): string
    {
        return 'order_price_rule';
    }

    public static function getLabel(): string
    {
        return 'Стоимость заказа';
    }
}
