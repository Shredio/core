<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use Shredio\Core\Intl\Language;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserEntity extends UserInterface
{

	public function getId(): AccountId;

	public function getLanguage(): Language;

}
