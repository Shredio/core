<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

use Symfony\Contracts\Translation\TranslatorInterface;

final class ErrorsPayload
{

	private bool $error = false;

	private bool $warning = false;

	/** @var ErrorPayload[] */
	private array $errors = [];

	/** @var WarningPayload[] */
	private array $warnings = [];

	/**
	 * @param WarningPayload[] $errors
	 */
	public function __construct(array $errors = [])
	{
		foreach ($errors as $error) {
			$this->addError($error);
		}
	}

	public function addError(WarningPayload $payload): void
	{
		if ($payload instanceof ErrorPayload) {
			$this->errors[] = $payload;
			$this->error = true;
		} else {
			$this->warnings[] = $payload;
			$this->warning = true;
		}
	}

	public function hasErrors(): bool
	{
		return $this->error;
	}

	public function hasWarnings(): bool
	{
		return $this->warning;
	}

	public function isOk(): bool
	{
		return !$this->error && !$this->warning;
	}

	public function decide(bool $throwsOnWarnings): bool
	{
		if ($this->hasErrors()) {
			return true;
		}

		return $throwsOnWarnings && $this->hasWarnings();
	}

	/**
	 * @return list<mixed[]>
	 */
	public function toArray(TranslatorInterface $translator, bool $debugMode = false): array
	{
		$payload = [];

		foreach ($this->errors as $error) {
			$array = $error->toArray($translator, $debugMode);

			if ($array) {
				$payload[] = $array;
			}
		}

		foreach ($this->warnings as $error) {
			$array = $error->toArray($translator, $debugMode);

			if ($array) {
				$payload[] = $array;
			}
		}

		return $payload;
	}

}
