<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Environment;

use Shredio\Core\Environment\AppEnvironment;
use Symfony\Component\HttpKernel\KernelInterface;

final readonly class SymfonyAppEnvironment implements AppEnvironment
{

	public function __construct(
		private KernelInterface $kernel,
	)
	{
	}

	public function isProduction(): bool
	{
		return in_array($this->kernel->getEnvironment(), ['prod', 'production'], true);
	}

	public function isDevelopment(): bool
	{
		return in_array($this->kernel->getEnvironment(), ['dev', 'development'], true);
	}

	public function isStaging(): bool
	{
		return in_array($this->kernel->getEnvironment(), ['stage', 'staging'], true);
	}

	public function isTesting(): bool
	{
		return $this->kernel->getEnvironment() === 'test';
	}

	public function isDebug(): bool
	{
		return $this->kernel->isDebug();
	}

}
