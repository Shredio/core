<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use JsonSerializable;
use Stringable;

final readonly class Symbol implements Stringable, JsonSerializable
{

	public string $value;

	public function __construct(string $symbol)
	{
		$this->value = strtoupper($symbol);
	}

	public static function tryFrom(string $symbol): ?self
	{
		$symbol = trim($symbol);

		if ($symbol === '') {
			return null;
		}

		return new self($symbol);
	}

	public static function fromUnion(string|Symbol $symbol): self
	{
		return is_string($symbol) ? new Symbol(trim($symbol)) : $symbol;
	}

	public function equals(Symbol $symbol): bool
	{
		return $this->value === $symbol->value;
	}

	public function __toString(): string
	{
		return $this->value;
	}

	public function jsonSerialize(): string
	{
		return $this->value;
	}

}
