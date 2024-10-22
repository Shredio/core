<?php declare(strict_types = 1);

namespace Shredio\Core\Common\Php;

use InvalidArgumentException;

final readonly class ClassName
{

	public ?string $namespace;

	public string $name;

	public function __construct(
		public string $fullyQualifiedName,
	)
	{
		if (!$this->fullyQualifiedName) {
			throw new InvalidArgumentException('Fully qualified class name cannot be empty');
		}

		$this->namespace = $this->extractNamespace($this->fullyQualifiedName);
		$this->name = $this->extractClassName($this->fullyQualifiedName);
	}

	private function extractNamespace(string $fullyQualifiedClassName): ?string
	{
		$pos = strrpos($fullyQualifiedClassName, '\\');

		if ($pos === false) {
			return null;
		}

		return substr($fullyQualifiedClassName, 0, $pos);
	}

	private function extractClassName(string $fullyQualifiedClassName): string
	{
		$pos = strrpos($fullyQualifiedClassName, '\\');

		if ($pos === false) {
			return $fullyQualifiedClassName;
		}

		return substr($fullyQualifiedClassName, $pos + 1);
	}

}
