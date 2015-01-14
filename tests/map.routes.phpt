<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/routing.php';

{ // check exception if map getting zero arguments
	Assert::exception('map', '\BadFunctionCallException', 'Invalid number of arguments.', 500);
}

{ // handle all request method (failover for 404 requests)
	map(
		$allFunction = function () {
		}
	);
	Assert::same(routes()->all, $allFunction);
}

{ // handle any request method
	map(
		'/',
		$anyFunction = function () {
		}
	);
	Assert::same(routes()->any, [['/', $anyFunction]]);
}

{ // handle GET request method
	map(
		'GET', '/',
		$getFunction = function () {
		}
	);
	Assert::same(routes()->GET, [['/', $getFunction]]);
}

{// handle POST request method
	map(
		'POST', '/',
		$postFunction = function () {
		}
	);
	Assert::same(routes()->POST, [['/', $postFunction]]);
}

{ // handle 404 errors
	map(
		404,
		$errorFunction = function () {
		}
	);

	Assert::same(routes()->error[404], $errorFunction);
}

{ // handle 500 errors
	map(
		500,
		$errorFunction = function () {

		}
	);
	Assert::same(routes()->error[500], $errorFunction);
}