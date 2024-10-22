<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

final class SerializerRecursiveGuard
{

	/** @var array<string, bool> */
	private array $heap = [];

	public function add(string $className): void
	{
		$this->heap[$className] = true;
	}

	public function isOk(string $className): bool
	{
		if (isset($this->heap[$className])) {
			unset($this->heap[$className]);

			return false;
		}

		$this->heap[$className] = true;

		return true;
	}

}
