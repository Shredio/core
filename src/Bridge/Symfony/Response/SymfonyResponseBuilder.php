<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Response;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Nette\Utils\Json;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Response;

final class SymfonyResponseBuilder
{

	/**
	 * @param array<string, string> $headers
	 */
	public function __construct(
		public int $statusCode = 200,
		private array $headers = [],
		private string $body = '',
	)
	{
	}

	public static function json(mixed $values): self
	{
		$headers = ['content-type' => 'application/json'];

		return new self(headers: $headers, body: Json::encode($values));
	}

	public static function badRequestResponse(string $message): Response
	{
		return (new self(400, body: $message))->build();
	}

	public static function redirectResponse(string|UriInterface $uri, int $code = 302): Response
	{
		return (new self($code, ['location' => (string) $uri]))->build();
	}

	public static function redirectPermanentResponse(string|UriInterface $uri): Response
	{
		return self::redirectResponse($uri, 301);
	}

	public static function forbiddenResponse(string $string): Response
	{
		return (new self(403, body: $string))->build();
	}

	/**
	 * @param array<string[]> $errors
	 */
	public static function unprocessableEntity(array $errors): Response
	{
		return (new self(422, ['content-type' => 'application/json'], Json::encode($errors)))->build();
	}

	public static function notModified(): Response
	{
		return (new self(304))->build();
	}

	public static function empty(int $statusCode = 204): Response
	{
		return (new self($statusCode))->build();
	}

	public static function notFound(): Response
	{
		return (new self(404))->build();
	}

	public function cacheControl(?int $maxAge = null, bool $public = true): self
	{
		$directives = $public ? ['public'] : ['private'];

		if ($maxAge !== null) {
			$directives[] = 'max-age=' . max(1, $maxAge);
		}

		$this->headers['cache-control'] = implode(', ', $directives);

		return $this;
	}

	public function expires(DateTimeImmutable|DateInterval|null $duration): self
	{
		if ($duration) {
			if ($duration instanceof DateInterval) {
				$duration = (new DateTimeImmutable())->add($duration);
			}

			$duration = $duration->setTimezone(new DateTimeZone('GMT'));
			$this->headers['expires'] = $duration->format('D, d M Y H:i:s e');
		}

		return $this;
	}

	public function build(): Response
	{
		return new Response($this->body, $this->statusCode, $this->headers);
	}

	public function lastModified(?DateTimeInterface $lastUpdate): static
	{
		if ($lastUpdate) {
			$this->headers['last-modified'] = $lastUpdate->format('D, d M Y H:i:s \G\M\T');
			$this->headers['cache-control'] = 'must-revalidate';
		}

		return $this;
	}

	public function eTag(?string $eTag): static
	{
		if ($eTag) {
			$this->headers['etag'] = $eTag;
		}

		return $this;
	}

}
