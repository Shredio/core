<?php declare(strict_types = 1);

namespace Shredio\Core\Reporter;

use Throwable;

interface ExceptionReporter
{

	public function report(Throwable $exception): void;

}
