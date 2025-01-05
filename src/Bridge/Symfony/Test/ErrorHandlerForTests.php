<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

final class ErrorHandlerForTests implements ErrorRendererInterface
{

	public bool $throwExceptions = false;

	public function __construct(
		private readonly ErrorRendererInterface $decorated,
	)
	{
	}

	public function render(Throwable $exception): FlattenException
	{
		if ($this->throwExceptions) {
			throw $exception;
		}

		$flattenException = $this->decorated->render($exception);

		if ($flattenException->getStatusCode() === 500) {
			throw $exception;
		}

		return $flattenException;
	}

}
