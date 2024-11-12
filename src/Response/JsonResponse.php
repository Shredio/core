<?php declare(strict_types = 1);

namespace Shredio\Core\Response;

final class JsonResponse extends Response
{

	public function __construct(mixed $value)
	{
		parent::__construct(
			headers: [
				'Content-Type' => 'application/json',
			],
			body: json_encode($value, JSON_THROW_ON_ERROR),
		);
	}

}
