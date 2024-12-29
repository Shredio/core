<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Middleware;

use Psr\Http\Message\ResponseInterface;
use Shredio\Core\Bridge\Symfony\Http\LazyServerRequest;
use Shredio\Core\Package\Packager;
use Shredio\Core\Package\Response\SourceResponse;
use Shredio\Core\Package\Response\SourcesResponse;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class PackagingMiddleware implements EventSubscriberInterface
{

	public function __construct(
		private Packager $packager,
		private HttpMessageFactoryInterface $httpMessageFactory,
	)
	{
	}

	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::VIEW => ['onKernelView', 10],
		];
	}

	public function onKernelView(ViewEvent $event): void
	{
		$response = $event->getControllerResult();

		if (!$response instanceof ResponseInterface) {
			return;
		}

		if ($response instanceof SourceResponse) {
			$response = $this->packager->packSource(
				$response->getSource(),
				$response->getInstructions(),
				$response,
				new LazyServerRequest(fn () => $this->httpMessageFactory->createRequest($event->getRequest())),
			);
		} else if ($response instanceof SourcesResponse) {
			$response = $this->packager->packSource(
				$response->getSources(),
				$response->getInstructions(),
				$response,
				new LazyServerRequest(fn () => $this->httpMessageFactory->createRequest($event->getRequest())),
			);
		}

		$event->setControllerResult($response);
	}

}
