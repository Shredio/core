<?php declare(strict_types = 1);

namespace Shredio\Core\Response;

use Nyholm\Psr7\Response as NyholmResponse;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class Response implements ResponseInterface
{

    protected NyholmResponse $decorate;

    /**
     * @param array<string, string> $headers
     * @param string|resource|StreamInterface|null $body
     */
	public function __construct(
		int $status = 200,
		array $headers = [],
		mixed $body = null,
		string $version = '1.1',
		?string $reason = null,
	)
	{
        $this->decorate = new NyholmResponse($status, $headers, $body, $version, $reason);
    }

    public function getProtocolVersion(): string
    {
        return $this->decorate->getProtocolVersion();
    }

	/**
	 * @return MessageInterface&static
	 */
    public function withProtocolVersion(string $version): MessageInterface
    {
        $new = clone $this;
        $new->decorate = $this->decorate->withProtocolVersion($version);

        return $new;
    }

	/**
	 * @return string[][]
	 */
    public function getHeaders(): array
    {
        return $this->decorate->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->decorate->hasHeader($name);
    }

	/**
	 * @return string[]
	 */
    public function getHeader(string $name): array
    {
        return $this->decorate->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->decorate->getHeaderLine($name);
    }

	/**
	 * @param string|string[] $value
	 * @return MessageInterface&static
	 */
    public function withHeader(string $name, $value): MessageInterface
    {
        $new = clone $this;
        $new->decorate = $this->decorate->withHeader($name, $value);

        return $new;
    }

	/**
	 * @param string|string[] $value
	 * @return MessageInterface&static
	 */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $new = clone $this;
        $new->decorate = $this->decorate->withAddedHeader($name, $value);

        return $new;
    }

	/**
	 * @return MessageInterface&static
	 */
    public function withoutHeader(string $name): MessageInterface
    {
        $new = clone $this;
        $new->decorate = $this->decorate->withoutHeader($name);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->decorate->getBody();
    }

	/**
	 * @return MessageInterface&static
	 */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->decorate = $this->decorate->withBody($body);

        return $new;
    }

    public function getStatusCode(): int
    {
        return $this->decorate->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->decorate = $this->decorate->withStatus($code, $reasonPhrase);

        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->decorate->getReasonPhrase();
    }
}
