<?php declare(strict_types = 1);

use Nette\PhpGenerator\PhpFile;

require __DIR__ . '/../vendor/autoload.php';

$link = 'https://gist.githubusercontent.com/ksafranski/2973986/raw/5fda5e87189b066e11c1bf80bbfbecb556cf2cc1/Common-Currency.json';

$values = [
	'GBX' => ['GBX', false],
	'GBp' => ['GBX', false],
];
$currenciesBefore = ['USD', 'EUR', 'JPY', 'GBP', 'PHP', 'CNY', 'KRW'];

foreach (json_decode(file_get_contents($link), true) as $item) {
	$values[$item['code']] = [$item['symbol'], in_array($item['code'], $currenciesBefore, true)];
}

_generateClass(__DIR__ . '/../src/Formatter/CurrencyDatabase.php', 'Shredio\\Core\\Formatter', 'CurrencyDatabase', $values);

function _generateClass(string $filePath, string $namespace, string $className, array $values): void {
	$file = new PhpFile();
	$file->setStrictTypes();
	$class = $file->addNamespace($namespace)->addClass($className);

	$class->setFinal();

	$class->addConstant('Currencies', $values)
		->setFinal()
		->setProtected()
		->setComment('@var array<string, array{string, bool}>')
		->setType('array');

	$method = $class->addMethod('getCurrencyConfiguration');
	$method->setPublic();
	$method->setStatic();
	$method->addComment('@return array{symbol: string, isBefore: bool}|null');
	$method->setReturnType('?array');
	$method->addParameter('currency')
		->setType('string');

	$method->addBody('if (!($config = self::Currencies[$currency] ?? null)) {');
	$method->addBody("\t" . 'return null;');
	$method->addBody('}');
	$method->addBody('');
	$method->addBody('return [');
	$method->addBody("\t" . "'symbol' => \$config[0],");
	$method->addBody("\t" . "'isBefore' => \$config[1],");
	$method->addBody('];');

	file_put_contents($filePath, $file);
}
