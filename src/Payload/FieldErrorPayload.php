<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

final class FieldErrorPayload implements ErrorPayload
{

	public function __construct(
		public readonly string $message,
		public readonly string $field,
	)
	{
	}

	/**
	 * @param bool $debugMode
	 * @return mixed[]
	 */
	public function toArray(bool $debugMode = false): array
	{
		return [
			'message' => $this->message,
			'field' => $this->field,
		];
	}

}
