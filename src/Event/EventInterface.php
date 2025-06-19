<?php

declare(strict_types=1);

namespace Zepzeper\Torol\Event;

interface EventInterface
{
	/**
	 * Get the name of the event.
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Event data.
	 *
	 * @return array
	 */
	public function getData(): array;

	/**
	 * Stop the event propagation.
	 *
	 * @return string
	 */
	public function stopPropagation(): void;

	/**
	 * Check if the event propagation has been stopped.
	 *
	 * @return bool
	 */
	public function isPropagationStopped(): bool;
}
