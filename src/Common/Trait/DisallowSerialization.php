<?php declare(strict_types = 1);

namespace Shredio\Core\Common\Trait;

use LogicException;

trait DisallowSerialization
{

	public function __serialize(): never
	{
		throw new LogicException(sprintf('Serialization is not supported for class %s', static::class));
	}

}
