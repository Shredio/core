<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Shredio\Auth\Entity\InMemoryUserEntity;
use Shredio\Core\Rest\Test\FakeRequest;
use Shredio\Core\Rest\Test\FakeResponse;
use Shredio\Core\Rest\Test\FakeRestClient;
use Shredio\Core\Rest\Test\FakeRestClientFactory;
use Shredio\Core\Security\InMemoryUser;
use Shredio\Core\Test\Authentication\Actor;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait HttpRestEnvironment // @phpstan-ignore-line
{

	use WebEnvironment;

	#[Before]
	protected function setUpHttpRest(): void
	{
		TestHelperAccessor::get($this)->internals->initialize();
	}

	#[After]
	protected function tearDownHttpRest(): void
	{
		TestHelperAccessor::get($this)->internals->finalize();
	}

	/**
	 * @param array{class-string, non-empty-string}|null $controllerAction
	 */
	protected function fakeRestHttp(?array $controllerAction = null): FakeRestClient
	{
		$client = TestHelper::getClient($this->getKernel());
		self::getClient($client); // rewrite the client

		$client->disableReboot();

		/** @var UrlGeneratorInterface $urlGenerator */
		$urlGenerator = $client->getKernel()->getContainer()->get('router');
		$psr17Factory = new Psr17Factory();
		$psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
		$testHelper = TestHelperAccessor::get($this);

		$factory = new FakeRestClientFactory($this, function (FakeRequest $request) use ($client, $urlGenerator, $psrHttpFactory, $testHelper): FakeResponse {
			$testBench = TestHelper::getTestBench($client->getKernel());
			$language = null;

			if ($actor = $request->actor) {
				$testHelper->internals->tryFillActor($actor);

				if ($signedActor = $actor->getSignedActor()) {
//					$testBench->loginUser(new InMemoryUser($signedActor->getId()->toOriginal(), $signedActor->getRoles(), $signedActor->getLanguage()));
					$testBench->loginUser(new InMemoryUserEntity((string) $signedActor->getId()->toOriginal(), $signedActor->getRoles()));
					$language = $signedActor->getLanguage()->value;
				}
			}

			$url = $urlGenerator->generate($request->controllerMetadata->getRouteName($request->endpointMetadata), $request->parameters);
			$server = [];

			foreach ($request->headers as $name => $values) {
				$server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = implode(', ', $values);
			}

			if ($language !== null) {
				$server['HTTP_X_LANGUAGE'] = $language;
			}

			$cookieJar = $client->getCookieJar();
			$cookieJar->clear();

			foreach ($request->cookies as $cookie) {
				$cookieJar->set(new Cookie($cookie->name, $cookie->value));
			}

			if ($request->query) {
				if (str_contains($url, '?')) {
					$url .= '&' . http_build_query($request->query);
				} else {
					$url .= '?' . http_build_query($request->query);
				}
			}

			$client->request(
				$request->method,
				$url,
				server: $server,
				content: $request->body?->getContents(),
				changeHistory: false,
			);

			$testBench->reset();

			return new FakeResponse($psrHttpFactory->createResponse($client->getResponse()));
		});

		$httpClient = $factory->create($controllerAction);

		if ($actor = $testHelper->internals->getActorToSignIn()) {
			$httpClient->withActor($actor);
		}

		return $httpClient;
	}

	public function createActor(Actor $actor): Actor
	{
		return TestHelperAccessor::get($this)->internals->createActor($actor);
	}

}
