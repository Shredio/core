<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Error;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

final readonly class SerializerErrorRenderFormats
{

	public function __construct(
		private RequestStack $requestStack,
	)
	{
	}

	public function __invoke(FlattenException $exception): string
	{
		if (!$request = $this->requestStack->getCurrentRequest()) {
			throw new NotEncodableValueException();
		}

		$format = $request->getPreferredFormat('json');

		if ($format === 'html') {
			$format = 'json';
		}

		return $format ?? 'json';
	}

}
