<?php declare(strict_types = 1);

namespace Shredio\Core\Cache\Http;

use DateTimeImmutable;

interface HttpCache
{

	public const string ExpiresFormat = 'D, d M Y H:i:s \G\M\T';

	public function getLastUpdate(): ?DateTimeImmutable;

	public function getExpiration(): ?DateTimeImmutable;

	public function actualize(): void;

}
