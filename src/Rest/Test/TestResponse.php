<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Shredio\Core\Rest\Test\Assert\JsonArrayAssertions;
use Shredio\Core\Rest\Test\Assert\JsonMultiArrayAssertions;
use Shredio\Core\Test\Assert\HttpExpectation;

final class TestResponse
{

	/** @var array<string, string> */
	private array $cookies;
	
	public function __construct(
		private readonly ResponseInterface $response, 
	)
	{
		$this->cookies = $this->fetchCookies($this->response->getHeader('Set-Cookie'));
	}

	public function getStatusCode(): int
	{
		return $this->response->getStatusCode();
	}

	/**
	 * @return string[][]
	 */
	public function getHeaders(): array
	{
		return $this->response->getHeaders();
	}

	public function assertHasHeader(string $name, ?string $value = null): self
	{
		TestCase::assertTrue(
			$this->response->hasHeader($name),
			\sprintf('Response does not contain header with name [%s].', $name)
		);

		$headerValue = $this->response->getHeaderLine($name);

		if ($value) {
			TestCase::assertSame(
				$value,
				$headerValue,
				\sprintf("Header [%s] was found, but value [%s] does not match [%s].", $name, $headerValue, $value)
			);
		}

		return $this;
	}

	public function assertHeaderMissing(string $name): self
	{
		TestCase::assertFalse(
			$this->response->hasHeader($name),
			\sprintf('Response contains header with name [%s].', $name)
		);

		return $this;
	}

	public function assertStatus(int $status): self
	{
		TestCase::assertSame(
			$this->response->getStatusCode(),
			$status,
			\sprintf(
				"Received response status code [%s : %s] but expected %s. Body: %s",
				$this->response->getStatusCode(),
				$this->response->getReasonPhrase(),
				$status,
				$this->response->getBody(),
			)
		);

		return $this;
	}

	public function assertOk(): self
	{
		return $this->assertStatus(200);
	}

	public function assertNotModified(): self
	{
		return $this->assertStatus(304);
	}

	public function assertCreated(): self
	{
		return $this->assertStatus(201);
	}

	public function assertAccepted(): self
	{
		return $this->assertStatus(202);
	}

	public function assertNoContent(int $status = 204): self
	{
		$this->assertStatus($status);

		TestCase::assertEmpty(
			$this->response->getBody()->getContents(),
			'Response content should be empty.'
		);

		return $this;
	}

	public function assertBadRequest(): self
	{
		return $this->assertStatus(400);
	}

	public function assertNotFound(): self
	{
		return $this->assertStatus(404);
	}

	public function assertForbidden(): self
	{
		return $this->assertStatus(403);
	}

	public function assertUnauthorized(): self
	{
		return $this->assertStatus(401);
	}

	public function assertUnprocessable(): self
	{
		return $this->assertStatus(422);
	}

	public function assertBodySame(string $needle): self
	{
		TestCase::assertSame(
			$needle,
			(string) $this->response->getBody(),
			\sprintf('Response is not same with [%s]', $needle)
		);

		return $this;
	}

	public function assertBodyEmpty(): self
	{
		TestCase::assertEmpty(
			$this->response->getBody()->getContents(),
			'Response body is not empty.'
		);

		return $this;
	}

	public function assertBodyNotSame(string $needle): self
	{
		TestCase::assertNotSame(
			$needle,
			(string) $this->response->getBody(),
			\sprintf('Response is same with [%s]', $needle)
		);

		return $this;
	}

	public function assertBodyContains(string $needle): self
	{
		TestCase::assertStringContainsString(
			$needle,
			(string) $this->response->getBody(),
			\sprintf('Response doesn\'t contain [%s]', $needle)
		);

		return $this;
	}

	public function assertCookieExists(string $key): self
	{
		TestCase::assertArrayHasKey(
			$key,
			$this->getCookies(),
			\sprintf('Response doesn\'t have cookie with name [%s]', $key)
		);

		return $this;
	}

	public function assertCookieMissed(string $key): self
	{
		TestCase::assertArrayNotHasKey(
			$key,
			$this->getCookies(),
			\sprintf('Response has cookie with name [%s]', $key)
		);

		return $this;
	}

	public function assertCookieSame(string $key, mixed $value): self
	{
		$this->assertCookieExists($key);

		TestCase::assertSame(
			$value,
			$this->cookies[$key],
			\sprintf('Response cookie with name [%s] is not equal.', $key)
		);

		return $this;
	}

	public function isRedirect(): bool
	{
		return \in_array($this->response->getStatusCode(), [201, 301, 302, 303, 307, 308]);
	}

	public function failed(): bool
	{
		$code = $this->response->getStatusCode();

		if ($code >= 200 && $code < 300) {
			return false;
		}

		return true;
	}

	public function getOriginalResponse(): ResponseInterface
	{
		return $this->response;
	}

	public function __toString(): string
	{
		return (string) $this->response->getBody();
	}

	/**
	 * @return array<string, string>
	 */
	public function getCookies(): array
	{
		return $this->cookies;
	}

	/**
	 * @return mixed[]
	 */
	public function getJsonParsedBody(): array
	{
		return \json_decode(
			(string)$this->response->getBody(),
			true
		);
	}

	/**
	 * @param string[] $header
	 * @return array<string, string>
	 */
	private function fetchCookies(array $header): array
	{
		$result = [];
		foreach ($header as $line) {
			$cookie = explode('=', $line);
			$result[$cookie[0]] = rawurldecode(substr($cookie[1], 0, (int) strpos($cookie[1], ';')));
		}

		return $result;
	}

	public function createSingleArrayAssertions(): JsonArrayAssertions
	{
		$this->assertHasHeader('Content-Type', 'application/json');
		Assert::assertIsArray($body = $this->getJsonParsedBody());

		return new JsonArrayAssertions($body);
	}

	public function createMultiArrayAssertions(): JsonMultiArrayAssertions
	{
		$this->assertHasHeader('Content-Type', 'application/json');
		Assert::assertIsArray($body = $this->getJsonParsedBody());

		foreach ($body as $item) {
			Assert::assertIsArray($item);
		}

		return new JsonMultiArrayAssertions($body);
	}

	public function expect(HttpExpectation $expectHttp): void
	{
		$expectHttp->used = true;

		if ($expectHttp->statusCode !== null) {
			$this->assertStatus($expectHttp->statusCode);
		}
	}

}
