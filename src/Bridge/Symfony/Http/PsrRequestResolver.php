<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Traversable;

final class PsrRequestResolver implements ValueResolverInterface
{

	private const array SUPPORTED_TYPES = [
		ServerRequestInterface::class => true,
		RequestInterface::class => true,
		MessageInterface::class => true,
	];

	public function __construct(
		private readonly HttpMessageFactoryInterface $httpMessageFactory,
	) {
	}

	/**
	 * @return Traversable<LazyServerRequest>
	 */
	public function resolve(Request $request, ArgumentMetadata $argument): Traversable
	{
		if (!isset(self::SUPPORTED_TYPES[$argument->getType()])) {
			return;
		}

		yield new LazyServerRequest(fn () => $this->httpMessageFactory->createRequest($request));
	}

}
