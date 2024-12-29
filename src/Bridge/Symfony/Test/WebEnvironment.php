<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

trait WebEnvironment // @phpstan-ignore-line
{

	use KernelEnvironment;
	use WebTestTrait;

}
