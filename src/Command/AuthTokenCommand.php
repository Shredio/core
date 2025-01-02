<?php declare(strict_types = 1);

namespace Shredio\Core\Command;

use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AuthTokenProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'token:auth',
	description: 'Generate a new auth token',
)]
final class AuthTokenCommand extends Command
{

	public function __construct(
		private readonly AuthTokenProvider $tokenProvider,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addArgument('id', InputArgument::REQUIRED, 'User id.')
			->addOption('role', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'User roles.', [])
			->addOption('language', 'l', InputOption::VALUE_REQUIRED, 'User language.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$id = $input->getArgument('id');
		$roles = $this->getRoles($input);
		$language = $this->getLanguage($input);

		if (!is_string($id)) {
			$output->writeln('ID must be a string.');

			return self::FAILURE;
		}

		if ($roles) {
			$token = $this->tokenProvider->createForApi($id, $roles, $language ?? Language::English);
		} else {
			$token = $this->tokenProvider->create($id);
		}

		$output->writeln($token->getId());

		return self::SUCCESS;
	}

	/**
	 * @return string[]|null
	 */
	private function getRoles(InputInterface $input): ?array
	{
		$roles = $input->getOption('role');

		if (!is_array($roles)) {
			return null;
		}

		if (!$roles) {
			return null;
		}

		return $roles;
	}

	private function getLanguage(InputInterface $input): ?Language
	{
		$language = $input->getOption('language');

		if (!is_string($language)) {
			return null;
		}

		if (!$language) {
			return null;
		}

		return Language::tryFrom($language);
	}

}
