<?php

namespace Zepzeper\Torol\Dispatcher;

use Zepzeper\Torol\Event\EventInterface;

class EventDispatcher implements EventDispatcherInterface
{

	/**
	 * @var array<string, list<callable>> Stores listeners indexed by event eventName.
	 */
	private array $listeners = [];

	/**
	 * {@inheritdoc}
	 */
	public function addListener(string $eventName, callable $listener): void
	{
		if (!isset($this->listeners[$eventName])) {
			$this->listeners[$eventName] = [];
		}

		$this->listeners[$eventName][] = $listener;
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(EventInterface $event): EventInterface
	{
		$eventName = $event->getName();

		if (!isset($this->listeners[$eventName])) {
			return $event; // No listeners found
		}

		foreach ($this->listeners[$eventName] as $listener) {
			if ($event->isPropagationStopped()) {
				break; // halted
			}

			// Call the listener
			$listener($event);
		}

		return $event;
	}

}
