<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

interface PaginationLinkGenerator
{

	/**
	 * @param non-empty-array<string, scalar|null> $parameters
	 */
	public function link(PaginationRequest $request, array $parameters): string;

}
