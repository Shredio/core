<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

final readonly class MessageErrorPayload implements ErrorPayload
{

	public function __construct(
		private string $message,
	)
	{
	}

	public function toArray(bool $debugMode = false): array
	{
		return [
			'message' => $this->message,
		];
	}

}
