<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

final class ErrorsPayload
{

	/**
	 * @param ErrorPayload[] $errors
	 */
	public function __construct(
		private array $errors = [],
	)
	{
	}

	public function addError(ErrorPayload $payload): void
	{
		$this->errors[] = $payload;
	}

	/**
	 * @return list<mixed[]>
	 */
	public function toArray(bool $debugMode = false): array
	{
		$payload = [];

		foreach ($this->errors as $error) {
			$array = $error->toArray($debugMode);

			if ($array) {
				$payload[] = $array;
			}
		}

		return $payload;
	}

}
