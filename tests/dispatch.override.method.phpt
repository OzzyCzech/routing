<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/routing.php';

class Test {
	public $ok = false;

	function __invoke() {
		$this->ok = true;
	}
}

{ // POST _method uppercase
	$_SERVER['REQUEST_URI'] = '/_method_uppercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST['_method'] = 'GET';

	map('GET', '/_method_uppercase', $test = new Test);
	dispatch();
	Assert::true($test->ok);
}

{ // POST _method lovercase
	$_SERVER['REQUEST_URI'] = '/_method_lovercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST['_method'] = 'get';

	map('GET', '/_method_lovercase', $test = new Test);
	dispatch();
	Assert::true($test->ok);
}

{ // HTTP_X_HTTP_METHOD_OVERRIDE method uppercase
	$_SERVER['REQUEST_URI'] = '/http_x_http_method_overrode_uppercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'GET';

	map('GET', '/http_x_http_method_overrode_uppercase', $test = new Test);
	dispatch();
	Assert::true($test->ok);
}

{ // HTTP_X_HTTP_METHOD_OVERRIDE merhod lovercase
	$_SERVER['REQUEST_URI'] = '/http_x_http_method_overrode_lovercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'get';

	map('GET', '/http_x_http_method_overrode_lovercase', $test = new Test);
	dispatch();
	Assert::true($test->ok);
}