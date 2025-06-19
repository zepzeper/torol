<?php

declare(strict_types=1);

namespace Zepzeper\Torol\Event;

abstract class AbstractEvent implements EventInterface
{
		private bool $propagationStopped = false;
		protected array $data;

    /**
     * @param array<mixed,mixed> $data
     */
    public function __construct(array $data = [])
		{
			$this->data = $data;
		}

		/**
		 * {@inheritdoc}
		 */
    public function getName(): string
    {
			return static::class;
    }

		/**
		 * {@inheritdoc}
		 */
    public function getData(): array
    {
			return $this->data;
    }

		/**
		 * {@inheritdoc}
		 */
    public function stopPropagation(): void
    {
			$this->propagationStopped = true;
    }

		/**
		 * {@inheritdoc}
		 */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

}
