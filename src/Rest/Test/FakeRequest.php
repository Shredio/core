<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

use Psr\Http\Message\StreamInterface;
use Shredio\Core\Rest\Metadata\ControllerMetadata;
use Shredio\Core\Rest\Metadata\EndpointMetadata;
use Shredio\Core\Test\Authentication\Actor;

final readonly class FakeRequest
{

	/**
	 * @param array<string, mixed> $parameters
	 * @param array<string, mixed> $query
	 * @param array<string, mixed> $headers
	 */
	public function __construct(
		public string $method,
		public ControllerMetadata $controllerMetadata,
		public EndpointMetadata $endpointMetadata,
		public array $parameters = [],
		public array $query = [],
		public array $headers = [],
		public ?StreamInterface $body = null,
		public ?Actor $actor = null,
	)
	{
	}

}
