<?php

namespace Torol;

class Row
{
	/**
	 * The underlying data for the row
	 *
	 * @param array<string, mixed> $data
	 */
	private array $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Get a value from the row by its key.
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->has($key) ? $this->data[$key] : $default;
	}

	/**
	 * Set a value on the row. Returns the Row instance for fluent chaining.
	 */
	public function set(string $key, mixed $value): self
	{
		$this->data[$key] = $value;

		return $this;
	}

	/**
	 * Removes an array key.
	 */
	public function remove(string $key): self
	{
		unset($this->data[$key]);

        return $this;
	}

	/**
	 * Check if a key exists in the row's data.
	 */
	public function has(string $key): bool
	{
		return array_key_exists($key, $this->data);
	}

	/**
	 * Get the entire row's data as an associative array.
	 */
	public function toArray(): array
	{
		return $this->data;
	}
}
