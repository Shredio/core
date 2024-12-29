<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Assert;

final class HttpExpectation
{

	/** @internal */
	public bool $used = false;

	public function __construct(
		public readonly ?int $statusCode = null,
	)
	{
	}

}
