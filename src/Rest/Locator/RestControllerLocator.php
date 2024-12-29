<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Locator;

use InvalidArgumentException;
use ReflectionClass;
use Shredio\Core\Rest\Attribute\Controller;
use Shredio\Core\Rest\Metadata\ControllerMetadata;
use Shredio\Core\Rest\Metadata\ControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultEndpointMetadataFactory;
use Symfony\Component\Finder\Finder;

final readonly class RestControllerLocator
{

	private ControllerMetadataFactory $controllerMetadataFactory;

	private string $regexAttribute;

	public function __construct(?ControllerMetadataFactory $controllerMetadataFactory = null)
	{
		$this->controllerMetadataFactory = $controllerMetadataFactory ?? new DefaultControllerMetadataFactory(new DefaultEndpointMetadataFactory());
		$this->regexAttribute = preg_quote(Controller::class, '#');
	}

	/**
	 * @return ControllerMetadata[]
	 */
	public function locate(string $directory): array
	{
		$finder = new Finder();

		$files = $finder->files()->in($directory)->name('*.php')->getIterator();
		$controllers = [];

		foreach ($files as $file) {
			$contents = $file->getContents();

			$className = $this->getClassName($contents);

			if (!$className) {
				continue;
			}

			$metadata = $this->controllerMetadataFactory->create(new ReflectionClass($className));

			if (!$metadata) {
				continue;
			}

			$controllers[] = $metadata;
		}

		return $controllers;
	}

	/**
	 * @return class-string|false
	 */
	private function getClassName(string $contents): string|false
	{
		if (!preg_match(sprintf('#use\s+%s(?:;|\s)#', $this->regexAttribute), $contents)) {
			return false;
		}

		$class = false;
		$namespace = false;
		$tokens = token_get_all($contents);

		if (count($tokens) === 1 && $tokens[0][0] === T_INLINE_HTML) {
			throw new InvalidArgumentException(
				sprintf('The file does not contain PHP code. Did you forget to add the "<?php" start tag at the beginning of the file?'));
		}

		$nsTokens = [T_NS_SEPARATOR => true, T_STRING => true];
		if (defined('T_NAME_QUALIFIED')) {
			$nsTokens[T_NAME_QUALIFIED] = true;
		}
		for ($i = 0; isset($tokens[$i]); ++$i) {
			$token = $tokens[$i];
			if (!isset($token[1])) {
				continue;
			}

			if (true === $class && T_STRING === $token[0]) {
				/** @var class-string */
				return $namespace.'\\'.$token[1];
			}

			if (true === $namespace && isset($nsTokens[$token[0]])) {
				$namespace = $token[1];
				while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
					$namespace .= $tokens[$i][1];
				}
				$token = $tokens[$i];
			}

			if (T_CLASS === $token[0]) {
				// Skip usage of ::class constant and anonymous classes
				$skipClassToken = false;
				for ($j = $i - 1; $j > 0; --$j) {
					if (!isset($tokens[$j][1])) {
						if ('(' === $tokens[$j] || ',' === $tokens[$j]) {
							$skipClassToken = true;
						}
						break;
					}

					if (T_DOUBLE_COLON === $tokens[$j][0] || T_NEW === $tokens[$j][0]) {
						$skipClassToken = true;
						break;
					} elseif (!in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT])) {
						break;
					}
				}

				if (!$skipClassToken) {
					$class = true;
				}
			}

			if (T_NAMESPACE === $token[0]) {
				$namespace = true;
			}
		}

		return false;
	}

}
