<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Error;

use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Exception\HttpException;
use Shredio\Core\Payload\ErrorsPayloadProcessor;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'onKernelException', priority: 129)]
final class ErrorListener
{

	public bool $catchExceptions;

	public function __construct(
		private readonly ErrorsPayloadProcessor $errorsPayloadProcessor,
		private readonly AppEnvironment $appEnv,
	)
	{
		$this->catchExceptions = !$this->appEnv->isDebugMode() || $this->appEnv->isTesting();
	}

	public function onKernelException(ExceptionEvent $event): void
	{
		if (!$this->catchExceptions) {
			return;
		}

		$throwable = $event->getThrowable();

		if ($throwable instanceof HttpException) {
			$event->stopPropagation();
			$event->setResponse($this->createResponseForHttpException($throwable));
		} else if ($throwable instanceof RequestExceptionInterface) {
			$event->stopPropagation();
			$event->setResponse($this->createResponseForBadRequestException($throwable));
		} else if ($throwable instanceof SymfonyHttpException) {
			$event->stopPropagation();
			$event->setResponse($this->createResponseForSymfonyHttpException($throwable));
		}
	}

	private function createResponseForHttpException(HttpException $exception): Response
	{
		$payload = $this->errorsPayloadProcessor->process($exception->getPayload());

		if (!$payload) {
			return new Response('', $exception->getHttpCode());
		}

		return new Response(json_encode($payload, JSON_THROW_ON_ERROR), $exception->getHttpCode(), [
			'Content-Type' => 'application/json',
		]);
	}

	private function createResponseForBadRequestException(RequestExceptionInterface $throwable): Response
	{
		if (!$this->includeInternalMessage() || !$throwable instanceof Throwable) {
			return new Response('', 400);
		}

		return new Response($throwable->getMessage(), 400);
	}

	private function includeInternalMessage(): bool
	{
		return !$this->appEnv->isRuntimeProduction();
	}

	private function createResponseForSymfonyHttpException(SymfonyHttpException $throwable): Response
	{
		if (!$this->includeInternalMessage()) {
			return new Response('', $throwable->getStatusCode());
		}

		return new Response($throwable->getMessage(), $throwable->getStatusCode(), $throwable->getHeaders());
	}

}
