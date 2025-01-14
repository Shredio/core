<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

interface PaginationPointer
{

	public function getLimit(PaginationRequest $request): int;

	public function getParameterName(): string;

}
