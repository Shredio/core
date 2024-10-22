<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use Exception;

final class UserNotLoggedIn extends Exception
{

	public function __construct()
	{
		parent::__construct('User is not logged in.');
	}

}
