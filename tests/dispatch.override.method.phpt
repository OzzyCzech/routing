<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';

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

	\route\map('GET', '/_method_uppercase', $test = new Test);
	\app\dispatch();
	Assert::true($test->ok);
}

{ // POST _method lovercase
	$_SERVER['REQUEST_URI'] = '/_method_lovercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST['_method'] = 'get';

	\route\map('GET', '/_method_lovercase', $test = new Test);
	\app\dispatch();
	Assert::true($test->ok);
}

{ // HTTP_X_HTTP_METHOD_OVERRIDE method uppercase
	$_SERVER['REQUEST_URI'] = '/http_x_http_method_overrode_uppercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'GET';

	\route\map('GET', '/http_x_http_method_overrode_uppercase', $test = new Test);
	\app\dispatch();
	Assert::true($test->ok);
}

{ // HTTP_X_HTTP_METHOD_OVERRIDE merhod lovercase
	$_SERVER['REQUEST_URI'] = '/http_x_http_method_overrode_lovercase';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'get';

	\route\map('GET', '/http_x_http_method_overrode_lovercase', $test = new Test);
	\app\dispatch();
	Assert::true($test->ok);
}