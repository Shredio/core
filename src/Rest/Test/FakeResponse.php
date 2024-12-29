<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

use Psr\Http\Message\ResponseInterface;

final readonly class FakeResponse
{

	public function __construct(
		public ResponseInterface $response,
	)
	{
	}

}
