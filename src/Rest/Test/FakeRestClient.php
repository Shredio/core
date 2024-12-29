<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

use LogicException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Shredio\Core\Rest\Metadata\ControllerMetadata;
use Shredio\Core\Rest\Metadata\EndpointMetadata;
use Shredio\Core\Rest\Test\Attribute\TestControllerMethod;
use Shredio\Core\Test\Authentication\Actor;

final class FakeRestClient
{

	/** @var array<string, mixed> */
	private array $parameters = [];

	/** @var array<string, mixed> */
	private array $query = [];

	/** @var array<string, mixed[]> */
	private array $headers = [];

	private ?StreamInterface $body = null;

	/** @var callable(FakeRequest $request): FakeResponse */
	private $request;

	private ?Actor $actor = null;

	/**
	 * @param callable(FakeRequest $request): FakeResponse $request
	 */
	public function __construct(
		private readonly ControllerMetadata $controllerMetadata,
		private readonly string $method,
		callable $request,
	)
	{
		$this->request = $request;
	}

	/**
	 * @param mixed[] $query
	 */
	public function withQuery(array $query): self
	{
		$this->query = array_filter($query, fn ($value) => $value !== null);

		return $this;
	}

	/**
	 * @param array<string, mixed> $parameters
	 */
	public function withParameters(array $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	/**
	 * @param array<string, string[]> $headers
	 */
	public function withHeaders(array $headers): self
	{
		$this->headers = $headers;

		return $this;
	}

	public function withHeader(string $name, mixed $value): self
	{
		$this->headers[$name] = [$value];

		return $this;
	}

	public function withAppendHeader(string $name, mixed $value): self
	{
		$this->headers[$name][] = $value;

		return $this;
	}

	public function withJsonBody(mixed $data): self
	{
		if (!$data instanceof StreamInterface) {
			$data = Stream::create(json_encode($data, JSON_THROW_ON_ERROR));
		}

		$this->body = $data;
		$this->withHeader('Content-Length', $data->getSize());
		$this->withHeader('Content-Type', 'application/json');
		$this->withHeader('Accept', 'application/json');

		return $this;
	}

	public function send(): TestResponse
	{
		$endpoint = $this->controllerMetadata->getEndpoint($this->method);
		$result = ($this->request)(new FakeRequest(
			$this->getHttpMethod($endpoint),
			$this->controllerMetadata,
			$endpoint,
			$this->parameters,
			$this->query,
			$this->headers,
			$this->body,
			$this->actor,
		));

		return new TestResponse($result->response);
	}

	private function getHttpMethod(EndpointMetadata $endpoint): string
	{
		if ($endpoint->isCreate()) {
			return 'POST';
		}

		if ($endpoint->isRead()) {
			return 'GET';
		}

		if ($endpoint->isReadCollection()) {
			return 'GET';
		}

		if ($endpoint->isUpdate()) {
			return 'PATCH';
		}

		if ($endpoint->isDelete()) {
			return 'DELETE';
		}

		$methods = $endpoint->attribute->getMethods();

		if (count($methods) !== 1) {
			throw new LogicException('Custom endpoint must have exactly one method or specify method in ' . TestControllerMethod::class);
		}

		return strtoupper($methods[array_key_first($methods)]);
	}

	public function withActor(Actor $actor): self
	{
		$this->actor = $actor;

		return $this;
	}

}
