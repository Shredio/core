<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use InvalidArgumentException;
use Stringable;

final readonly class AccountId implements Stringable
{

	private function __construct(
		private int $id,
	)
	{
	}

	public function toOriginal(): string|int // @phpstan-ignore-line
	{
		return $this->id;
	}

	/**
	 * @return non-empty-string
	 */
	public function toString(): string
	{
		return (string) $this->id;
	}

	/**
	 * @return non-empty-string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	public static function from(string|int $id): self
	{
		return new self((int) self::parse($id));
	}

	public static function parse(string|int $id): string|int // @phpstan-ignore-line
	{
		if (!is_numeric($id)) {
			throw new InvalidArgumentException(sprintf('User ID must be numeric, %s given.', $id));
		}

		return (int) $id;
	}

	public function equals(string|int|self|null $value): bool
	{
		if ($value === null) {
			return false;
		}

		if (!is_object($value)) {
			$value = self::from($value);
		}

		return $this->id === $value->id;
	}

}
