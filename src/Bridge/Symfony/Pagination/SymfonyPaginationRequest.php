<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Pagination;

use Shredio\Core\Pagination\PaginationRequest;
use Symfony\Component\HttpFoundation\Request;

final readonly class SymfonyPaginationRequest implements PaginationRequest
{

	/**
	 * @param array<string, mixed> $parameters
	 */
	public function __construct(
		public Request $request,
		public array $parameters = [],
	)
	{
	}

	public function getIntegerFromRequest(string $name, int $default): int
	{
		return $this->request->query->getInt($name, $default);
	}

}
