<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Commands;

use App\Packages\TriggerEvent\Exception\EventStatusNotFind;
use App\Packages\TriggerEvent\Repository\EventRepository;
use App\Packages\TriggerEvent\Service\TriggerService;
use Illuminate\Console\Command;

class HandleEventsCommand extends Command
{
    protected $signature = 'handle_events:all';
    protected $description = 'Обработка событий';
    private EventRepository $eventRepository;
    private TriggerService $triggerService;

    public function __construct(EventRepository $eventRepository, TriggerService $triggerService)
    {
        parent::__construct();
        $this->eventRepository = $eventRepository;
        $this->triggerService = $triggerService;
    }

    /**
     * @throws EventStatusNotFind
     */
    public function handle()
    {
        $events = $this->eventRepository->getCurrentEvents();

        foreach ($events as $event) {
            $this->triggerService->processEvent($event);
        }
    }
}
