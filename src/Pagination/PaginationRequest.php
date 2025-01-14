<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

interface PaginationRequest
{

	public function getIntegerFromRequest(string $name, int $default): int;

}
