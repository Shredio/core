<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Environment;

use Shredio\Core\Environment\AppEnvironment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

final readonly class SymfonyAppEnvironment implements AppEnvironment
{

	private const array ProductionEnvironments = ['prod' => true, 'production' => true];
	private const array DevelopmentEnvironments = ['dev' => true, 'development' => true];
	private const array StagingEnvironments = ['stage' => true, 'staging' => true];
	private const array TestingEnvironment = ['test' => true];
	private const array LocalEnvironment = ['local' => true];

	public function __construct(
		#[Autowire(param: 'kernel.environment')]
		private string $environment,
		#[Autowire(param: 'kernel.runtime_environment')]
		private string $runtimeEnvironment,
		#[Autowire(param: 'kernel.debug')]
		private bool $debugMode,
	)
	{
	}

	public function isProduction(): bool
	{
		return isset(self::ProductionEnvironments[$this->environment]);
	}

	public function isDevelopment(): bool
	{
		return isset(self::DevelopmentEnvironments[$this->environment]);
	}

	public function isStaging(): bool
	{
		return isset(self::StagingEnvironments[$this->environment]);
	}

	public function isTesting(): bool
	{
		return isset(self::TestingEnvironment[$this->environment]);
	}

	public function isRuntimeProduction(): bool
	{
		return isset(self::ProductionEnvironments[$this->runtimeEnvironment]);
	}

	public function isRuntimeLocal(): bool
	{
		return isset(self::LocalEnvironment[$this->runtimeEnvironment]);
	}

	public function isDebugMode(): bool
	{
		return $this->debugMode;
	}

}
