<?php declare(strict_types = 1);

namespace Shredio\Core\Environment;

interface AppEnvironment
{

	public function isProduction(): bool;

	public function isDevelopment(): bool;

	/**
	 * @deprecated Use isRuntimeStaging() instead, staging should be a production environment combined with a staging runtime environment
	 */
	public function isStaging(): bool;

	public function isTesting(): bool;

	public function isRuntimeProduction(): bool;

	public function isRuntimeStaging(): bool;

	public function isRuntimeLocal(): bool;

	public function isDebugMode(): bool;

}
