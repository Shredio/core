<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test\Attribute;

final readonly class ExpectStatusCode
{

	public function __construct(
		public int $code,
	)
	{
	}

}
