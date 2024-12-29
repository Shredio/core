<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class LazyServerRequest implements ServerRequestInterface
{

	private ?ServerRequestInterface $decorate = null;

	/** @var callable(): ServerRequestInterface */
	private mixed $factory;

	/**
	 * @param callable(): ServerRequestInterface $factory
	 */
	public function __construct(
		callable $factory,
	)
	{
		$this->factory = $factory;
	}

	private function getDecorate(): ServerRequestInterface
	{
		return $this->decorate ??= ($this->factory)();
	}

	public function getProtocolVersion(): string
	{
		return $this->getDecorate()->getProtocolVersion();
	}

	public function withProtocolVersion($version): MessageInterface
	{
		return $this->getDecorate()->withProtocolVersion($version);
	}

	public function getHeaders(): array
	{
		return $this->getDecorate()->getHeaders();
	}

	public function hasHeader($name): bool
	{
		return $this->getDecorate()->hasHeader($name);
	}

	public function getHeader($name): array
	{
		return $this->getDecorate()->getHeader($name);
	}

	public function getHeaderLine($name): string
	{
		return $this->getDecorate()->getHeaderLine($name);
	}

	public function withHeader($name, $value): MessageInterface
	{
		return $this->getDecorate()->withHeader($name, $value);
	}

	public function withAddedHeader($name, $value): MessageInterface
	{
		return $this->getDecorate()->withAddedHeader($name, $value);
	}

	public function withoutHeader($name): MessageInterface
	{
		return $this->getDecorate()->withoutHeader($name);
	}

	public function getBody(): StreamInterface
	{
		return $this->getDecorate()->getBody();
	}

	public function withBody(StreamInterface $body): MessageInterface
	{
		return $this->getDecorate()->withBody($body);
	}

	public function getRequestTarget(): string
	{
		return $this->getDecorate()->getRequestTarget();
	}

	public function withRequestTarget($requestTarget): RequestInterface
	{
		return $this->getDecorate()->withRequestTarget($requestTarget);
	}

	public function getMethod(): string
	{
		return $this->getDecorate()->getMethod();
	}

	public function withMethod($method): RequestInterface
	{
		return $this->getDecorate()->withMethod($method);
	}

	public function getUri(): UriInterface
	{
		return $this->getDecorate()->getUri();
	}

	public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
	{
		return $this->getDecorate()->withUri($uri, $preserveHost);
	}

	public function getServerParams(): array
	{
		return $this->getDecorate()->getServerParams();
	}

	public function getCookieParams(): array
	{
		return $this->getDecorate()->getCookieParams();
	}

	public function withCookieParams(array $cookies): ServerRequestInterface
	{
		return $this->getDecorate()->withCookieParams($cookies);
	}

	public function getQueryParams(): array
	{
		return $this->getDecorate()->getQueryParams();
	}

	public function withQueryParams(array $query): ServerRequestInterface
	{
		return $this->getDecorate()->withQueryParams($query);
	}

	public function getUploadedFiles(): array
	{
		return $this->getDecorate()->getUploadedFiles();
	}

	public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
	{
		return $this->getDecorate()->withUploadedFiles($uploadedFiles);
	}

	public function getParsedBody(): mixed
	{
		return $this->getDecorate()->getParsedBody();
	}

	public function withParsedBody($data): ServerRequestInterface
	{
		return $this->getDecorate()->withParsedBody($data);
	}

	public function getAttributes(): array
	{
		return $this->getDecorate()->getAttributes();
	}

	public function getAttribute($name, $default = null): mixed
	{
		return $this->getDecorate()->getAttribute($name, $default);
	}

	public function withAttribute($name, $value): ServerRequestInterface
	{
		return $this->getDecorate()->withAttribute($name, $value);
	}

	public function withoutAttribute($name): ServerRequestInterface
	{
		return $this->getDecorate()->withoutAttribute($name);
	}

}
