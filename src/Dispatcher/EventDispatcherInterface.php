<?php

declare(strict_types=1);

namespace Zepzeper\Torol\Dispatcher;

use Zepzeper\Torol\Event\EventInterface;

interface EventDispatcherInterface
{
		/**
     * Adds an event listener for a specific event.
     *
     * @param string   	$eventName The name of the event to listen for (e.g., UserCreatedEvent::class).
     * @param callable 	$listener  The listener callable that will be triggered when the event is dispatched.
		 * @param int   		$priority The priority of the event.
     * @return void
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void;

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param EventInterface $event The event object to dispatch.
     * @return EventInterface The dispatched event (possibly modified by listeners).
     */
		public function dispatch(EventInterface $event): EventInterface;
}
