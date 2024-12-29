<?php declare(strict_types = 1);

namespace Shredio\Core\Test;

use PHPUnit\Framework\Assert;

trait TestAsserts // @phpstan-ignore-line
{

	public function assertArrayAssocIntersect(iterable $expected, mixed $actual, string $message = ''): void
	{
		Assert::assertThat($actual, new Constraint\ArrayAssocIntersect($expected), $message);
	}

}
