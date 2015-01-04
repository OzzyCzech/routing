<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/phi.php';

class Test {
	public $ok = false;

	function __invoke() {
		$this->ok = true;
	}
}

{ // POST error handling
	$_SERVER['REQUEST_URI'] = '/some-example-url';
	$_SERVER['REQUEST_METHOD'] = 'GET';

	// 404 called
	\phi\map(404, $test = new Test);
	\phi\dispatch();
	Assert::true($test->ok);
}

{
	$_SERVER['REQUEST_URI'] = '/some-other-example-url';
	$_SERVER['REQUEST_METHOD'] = 'GET';
	// not 404 called
	\phi\map(
		404, function () {
		Assert::fail('404 handler function called.');
	}
	);
	\phi\map('/some-other-example-url', $test = new Test);
	\phi\dispatch();
	Assert::true($test->ok);
}

http_response_code(200); // fix exit code