<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';

class app {

	/** @var bool */
	public $ok = false;

	function __invoke($method, $path, $me) {
		Assert::same('GET', $method);
		Assert::same('an/example/url', $path);
		Assert::same($this, $me);

		$me->ok = true;
		// You can here handle all routes
		// case ($method) ... e.g.
	}

	function __destruct() {
		\app\dispatch($this);
		Assert::true($this->ok); // check if invoke...
	}
}

\route\map(new app);

$_SERVER['REQUEST_URI'] = '/an/example/url';
$_SERVER['REQUEST_METHOD'] = 'GET';
