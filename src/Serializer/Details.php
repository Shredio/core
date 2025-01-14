<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

interface Details
{

	/**
	 * @return mixed[]
	 */
	public function &getContext(): array;

	public function getFormat(): ?string;

}
