<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/phi.php';

class Test {
	/** @var bool */
	public $ok = false;

	/** @var array|null */
	public $args;

	function __invoke() {
		$this->args = func_get_args();
		$this->ok = true;
	}
}

{ // root path no params
	$_SERVER['REQUEST_URI'] = '/';
	$_SERVER['REQUEST_METHOD'] = 'GET';

	\phi\map('/', $test = new Test);
	\phi\dispatch();

	Assert::true($test->ok);
	Assert::true(empty($test->args));
}