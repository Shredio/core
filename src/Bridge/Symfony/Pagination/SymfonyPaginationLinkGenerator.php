<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Pagination;

use InvalidArgumentException;
use LogicException;
use Shredio\Core\Pagination\PaginationLinkGenerator;
use Shredio\Core\Pagination\PaginationRequest;
use Symfony\Component\Routing\RouterInterface;

final readonly class SymfonyPaginationLinkGenerator implements PaginationLinkGenerator
{

	public function __construct(
		private RouterInterface $router,
	)
	{
	}

	public function link(PaginationRequest $request, array $parameters): string
	{
		if (!$request instanceof SymfonyPaginationRequest) {
			throw new InvalidArgumentException(sprintf('Expected %s, got %s', SymfonyPaginationRequest::class, get_debug_type($request)));
		}

		$route = $request->request->attributes->getString('_route');
		$routeParams = $request->request->attributes->get('_route_params');

		if ($route === '') {
			throw new LogicException('Cannot generate pagination link, because the current request does not have a route.');
		}

		if (!is_array($routeParams)) {
			throw new LogicException('Cannot generate pagination link, because the current request does not have route parameters.');
		}

		return $this->router->generate($route, array_merge($routeParams, $request->parameters, $parameters));
	}

}
