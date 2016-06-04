<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';

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

{ // optional back slash on the end of url
	$_SERVER['REQUEST_URI'] = '/some-url-with-optional-end';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	\route\map('/some-url-with-optional-end/?', $test = new Test);
	\app\dispatch();

	Assert::true($test->ok);
}

{ // optional back slash on the end of url and integer
	$_SERVER['REQUEST_URI'] = '/some-parametter-in-url-and-123';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	\route\map('/some-parametter-in-url-and-/?<id:[0-9]+>', $test = new Test);

	\app\dispatch();
	Assert::true($test->ok);
	Assert::true(array_key_exists('id', reset($test->args)));
}

{ // optional back slash on the end of url and integer
	$_SERVER['REQUEST_URI'] = '/and-param/value';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	\route\map('/and-param/<param>', $test = new Test);

	\app\dispatch();
	Assert::true($test->ok);
	Assert::true(array_key_exists('param', reset($test->args)));
}
