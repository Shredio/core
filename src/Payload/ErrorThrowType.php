<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

enum ErrorThrowType
{

	case Validation;
	case BadRequest;

}
