<?php declare(strict_types = 1);

namespace Shredio\Core\Package;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ItemToPack
{

	public function __construct(
		public mixed $value,
		public ResponseInterface $response,
		public ServerRequestInterface $request,
	)
	{
	}

	public function withValue(mixed $value): self
	{
		return new self($value, $this->response, $this->request);
	}

	public function withResponse(ResponseInterface $response): self
	{
		return new self($this->value, $response, $this->request);
	}

}
