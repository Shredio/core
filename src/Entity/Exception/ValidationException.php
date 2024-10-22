<?php declare(strict_types = 1);

namespace Shredio\Core\Entity\Exception;

use RuntimeException;
use Shredio\Core\Exception\HttpException;

final class ValidationException extends RuntimeException implements HttpException
{

	/**
	 * @param mixed[] $errors
	 */
	public function __construct(
		private array $errors,
	)
	{
		parent::__construct('The given data was invalid.', 422);
	}

	public function getHttpCode(): int
	{
		return 422;
	}

	/**
	 * @return mixed[]
	 */
	public function getPayload(): array
	{
		return $this->errors;
	}

}
