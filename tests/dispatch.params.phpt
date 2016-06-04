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

{ // check custom param to dispatch
	$_SERVER['REQUEST_URI'] = '/add_param_to_dispatch';
	$_SERVER['REQUEST_METHOD'] = 'GET';

	\route\map('/add_param_to_dispatch', $test = new Test);
	\app\dispatch($param = 'add params to dispatch');

	// invoke route
	Assert::true($test->ok);
	// check params
	Assert::count(1, $test->args);
	Assert::same($param, $test->args[0]);
}

{ // check params order
	$_SERVER['REQUEST_URI'] = '/test_inurl_param/123';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	\route\map('/test_inurl_param/<id>', $test = new Test);
	\app\dispatch();

	// invoke route
	Assert::true($test->ok);
	Assert::count(1, $test->args);

	// first args contains params from route
	Assert::true(in_array('123', $test->args[0]));
	Assert::true(array_key_exists('id', $test->args[0]));
}


{ // add params to URL and also to dispatch
	$_SERVER['REQUEST_URI'] = '/test_inurl_param_and_dispatch/123';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	\route\map('/test_inurl_param_and_dispatch/<id>', $test = new Test);
	\app\dispatch($param = 'add params to dispatch');
	Assert::true($test->ok);
	Assert::count(2, $test->args);

	// first args contains params from route
	Assert::true(in_array('123', $test->args[0]));
	Assert::true(array_key_exists('id', $test->args[0]));
	Assert::same($param, $test->args[1]);
}
