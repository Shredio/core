<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

readonly class PagePaginationPointer implements PaginationPointer
{

	public function __construct(
		private int $limit,
		private string $pageParameter = 'page',
	)
	{
	}

	public function getLimit(PaginationRequest $request): int
	{
		return $this->limit;
	}

	public function getOffset(PaginationRequest $request): int
	{
		return ($this->getPage($request) - 1) * $this->getLimit($request);
	}

	public function getPage(PaginationRequest $request): int
	{
		$page = $request->getIntegerFromRequest($this->pageParameter, 1);

		if ($page < 1) {
			$page = 1;
		}

		return $page;
	}

	public function getNextPage(PaginationRequest $request): int
	{
		return $this->getPage($request) + 1;
	}

	public function getPrevPage(PaginationRequest $request): ?int
	{
		$page = $this->getPage($request) - 1;

		return $page < 1 ? null : $page;
	}

	public function getParameterName(): string
	{
		return $this->pageParameter;
	}

}
