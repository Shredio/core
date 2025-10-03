<?php declare(strict_types = 1);

namespace Shredio\Core\Command;

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
			->addOption('role', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'User roles.', []);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$id = $input->getArgument('id');
		$roles = $this->getRoles($input);

		if (!is_string($id)) {
			$output->writeln('ID must be a string.');

			return self::FAILURE;
		}

		if ($roles) {
			$token = $this->tokenProvider->createForApi($id, $roles);
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

}
