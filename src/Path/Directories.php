<?php declare(strict_types = 1);

namespace Shredio\Core\Path;

use OutOfBoundsException;

final class Directories
{

	public const string Root = 'root';
	public const string Cache = 'cache';
	public const string Log = 'log';

	/**
	 * @param array<string, string> $directories
	 */
	public function __construct(
		private array $directories = [],
	)
	{
	}

	public function has(string $name): bool
	{
		return isset($this->directories[$name]);
	}

	/**
	 * @param non-empty-string $name Directory alias, ie. "framework".
	 * @param string $path Directory path without ending slash.
	 */
	public function set(string $name, string $path): self
	{
		$this->directories[$name] = $path;

		return $this;
	}

	/**
	 * @param non-empty-string $name
	 * @throws OutOfBoundsException When no directory found.
	 */
	public function get(string $name): string
	{
		if (!isset($this->directories[$name])) {
			throw new OutOfBoundsException("Directory '$name' not found.");
		}

		return $this->directories[$name];
	}

	public function getRoot(): string
	{
		return $this->get(self::Root);
	}

	public function getCache(): string
	{
		return $this->get(self::Cache);
	}

	public function getLog(): string
	{
		return $this->get(self::Log);
	}

	/**
	 * @return array<string, string>
	 */
	public function getAll(): array
	{
		return $this->directories;
	}

}
