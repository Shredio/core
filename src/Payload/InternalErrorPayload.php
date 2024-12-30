<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

use Throwable;

final class InternalErrorPayload implements ErrorPayload
{

	/**
	 * @param mixed[] $payload
	 */
	private function __construct(
		private readonly ?ErrorPayload $original,
		private readonly array $payload,
	)
	{
	}

	/**
	 * @param mixed[] $payload
	 */
	public static function from(array $payload, ?ErrorPayload $original = null): self
	{
		return new self($original, $payload);
	}

	/**
	 * @param mixed[] $extra
	 */
	public static function fromThrowable(Throwable $throwable, ?ErrorPayload $original = null, array $extra = []): self
	{
		return new self($original, [
			'exception' => [
				'class' => $throwable::class,
				'message' => $throwable->getMessage(),
			],
			'trace' => $throwable->getTrace(),
			...$extra,
		]);
	}

	/**
	 * @return mixed[]
	 */
	public function toArray(bool $debugMode = false): array
	{
		$payload = $this->original?->toArray($debugMode) ?? [];

		if (!$debugMode) {
			return $payload;
		}

		return array_merge($payload, $this->payload);
	}

}
