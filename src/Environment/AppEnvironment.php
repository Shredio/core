<?php declare(strict_types = 1);

namespace Shredio\Core\Environment;

interface AppEnvironment
{

	public function isProduction(): bool;

	public function isDevelopment(): bool;

	public function isStaging(): bool;

	public function isTesting(): bool;

	public function isDebug(): bool;

}
