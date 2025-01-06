<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

/**
 * @template T
 */
final class LazyValue
{

	/** @var callable(): T */
	private readonly mixed $initializer;

	/** @var PropertyValue<T>|null */
	private ?PropertyValue $val = null;

	/**
	 * @param callable(): T $initializer
	 */
	public function __construct(callable $initializer)
	{
		$this->initializer = $initializer;
	}

	/**
	 * @return T
	 */
	public function getValue(): mixed
	{
		return ($this->val ??= new PropertyValue(($this->initializer)()))->value;
	}

	public function reset(): void
	{
		$this->val = null;
	}

}
