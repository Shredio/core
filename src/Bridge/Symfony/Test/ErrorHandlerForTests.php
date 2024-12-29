<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

final readonly class ErrorHandlerForTests implements ErrorRendererInterface
{

	public function __construct(
		private ErrorRendererInterface $decorated,
		private ?TestBench $testBench = null,
	)
	{
	}

	public function render(Throwable $exception): FlattenException
	{
		if ($this->testBench?->throwExceptions) {
			throw $exception;
		}

		$flattenException = $this->decorated->render($exception);

		if ($flattenException->getStatusCode() === 500) {
			throw $exception;
		}

		return $flattenException;
	}

}
