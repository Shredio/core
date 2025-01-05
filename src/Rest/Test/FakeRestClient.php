<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

use ArrayIterator;
use LogicException;
use MultipleIterator;
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

	/** @var array<string, FakeCookie> */
	private array $cookies = [];

	/** @var 'POST'|'GET'|'PATCH'|'DELETE'|'PUT'|null */
	private ?string $httpMethod = null;

	private ?StreamInterface $body = null;

	/** @var callable(FakeRequest $request): FakeResponse */
	private $request;

	private ?Actor $actor = null;

	/** @var mixed[]|null  */
	private ?array $identifiers = null;

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

	public function withIdentifiers(mixed ...$values): self
	{
		$this->identifiers = $values;

		return $this;
	}

	public function withCookie(FakeCookie $cookie): self
	{
		$this->cookies[$cookie->name] = $cookie;

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

	/**
	 * @param 'POST'|'GET'|'PATCH'|'DELETE'|'PUT' $method
	 * @return static
	 */
	public function withMethod(string $method): self
	{
		$this->httpMethod = $method;

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
			$this->httpMethod ?? $this->getHttpMethod($endpoint),
			$this->controllerMetadata,
			$endpoint,
			$this->resolveParameters($endpoint),
			$this->query,
			$this->headers,
			$this->cookies,
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

	/**
	 * @return array<string, mixed>
	 */
	private function resolveParameters(EndpointMetadata $endpoint): array
	{
		$identifiers = $this->identifiers;

		if ($identifiers === null) {
			return $this->parameters;
		}

		$routeParameters = $endpoint->getPattern($this->controllerMetadata)->getParameters();

		if (!$routeParameters) {
			throw new LogicException(
				sprintf(
					'Cannot call withIdentifier() for %s::%s, because endpoint does not have route parameters.',
					$this->controllerMetadata->className,
					$endpoint->name,
				),
			);
		}

		if (count($routeParameters) !== count($identifiers)) {
			throw new LogicException(
				sprintf(
					'Cannot call withIdentifier() for %s::%s, because endpoint has %d route parameters, but %d identifiers were provided.',
					$this->controllerMetadata->className,
					$endpoint->name,
					count($routeParameters),
					count($identifiers),
				),
			);
		}

		$parameters = $this->parameters;

		$multipleIterator = new MultipleIterator(MultipleIterator::MIT_NEED_ALL);
		$multipleIterator->attachIterator(new ArrayIterator($routeParameters), 'key');
		$multipleIterator->attachIterator(new ArrayIterator($identifiers), 'value');

		foreach ($multipleIterator as $value) {
			$parameters[$value['key']] = $value['value'];
		}

		return $parameters;
	}

}
