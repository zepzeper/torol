<?php

namespace Zepzeper\Torol\Dispatcher;

use Zepzeper\Torol\Event\EventInterface;

class EventDispatcher implements EventDispatcherInterface
{

	/**
	 * @var array<string, array<int, list<callable>>> Stores listeners indexed by event eventName.
	 */
	private array $listeners = [];

	/**
	 * @var array<string, bool> Stores listeners indexed by event eventName.
	 */
	private array $sorted = [];

	/**
	 * {@inheritdoc}
	 */
	public function addListener(string $eventName, callable $listener, int $priority = 0): void
	{
		if (!isset($this->listeners[$eventName])) {
			$this->listeners[$eventName] = [];
			$this->sorted[$eventName] = true;
		}
		if (!isset($this->listeners[$eventName][$priority])) {
			$this->listeners[$eventName][$priority] = [];
		}

		$this->listeners[$eventName][$priority][] = $listener;

		$this->sorted[$eventName] = false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeListener(string $eventName, callable $listener): void
	{
		if (!$this->listeners[$eventName]) { return; }

		foreach ($this->listeners[$eventName] as $listener) {

		}

		$this->sorted[$eventName] = false;
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

		if (!$this->sorted[$eventName]) {
			$this->doSortListener($eventName);
		}

		// Now iterate over the *sorted* listeners (which are flattened after doSortListeners)
		foreach ($this->listeners[$eventName] as $listener) {
			if ($event->isPropagationStopped()) {
				break; // halted
			}

			// Call the listener
			$listener($event);
		}

		return $event;
	}

	/**
	 * Sorts the listeners for a given event name by priority (highest first)
	 * and flattens the structure for efficient iteration during dispatch.
	 *
	 * @param string $eventName The name of the event whose listeners need sorting.
	 * @return void
	 */
	private function doSortListener(string $eventName): void 
	{
		$listenerByPriority = $this->listeners[$eventName];

		krsort($listenerByPriority);

		$sortedListeners = [];
		foreach ($listenerByPriority as $priorityListeners) {
			// Merge all listeners from this priority bucket into the final sorted list
			foreach ($priorityListeners as $listener) {
				$sortedListeners[] = $listener;
			}
		}

		$this->listeners[$eventName] = $sortedListeners;

		$this->sorted[$eventName] = true;
	}
}
