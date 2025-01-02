<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

final readonly class FakeCookie
{

	public function __construct(
		public string $name,
		public ?string $value,
	)
	{
	}

}
