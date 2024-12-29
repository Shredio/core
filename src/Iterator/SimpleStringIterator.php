<?php declare(strict_types = 1);

namespace Shredio\Core\Iterator;

final class SimpleStringIterator
{

	private readonly int $length;

	private int $pos = 0;

	public function __construct(
		public readonly string $str,
	)
	{
		$this->length = strlen($this->str);
	}

	public function next(): ?string
	{
		if ($this->pos >= $this->length) {
			return null;
		}

		return $this->str[$this->pos++];
	}

	public function current(): ?string
	{
		if ($this->pos >= $this->length) {
			return null;
		}

		return $this->str[$this->pos];
	}

	public function isEnd(): bool
	{
		return $this->pos >= $this->length;
	}

}
