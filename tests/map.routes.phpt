<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/phi.php';

{ // check exception if map getting zero arguments
	Assert::exception('\phi\map', '\BadFunctionCallException', 'Invalid number of arguments.', 500);
}

{ // handle all request method (failover for 404 requests)
	\phi\map(
		$allFunction = function () {
		}
	);
	Assert::same(\phi\routes()->all, $allFunction);
}

{ // handle any request method
	\phi\map(
		'/',
		$anyFunction = function () {
		}
	);
	Assert::same(\phi\routes()->any, [['/', $anyFunction]]);
}

{ // handle GET request method
	\phi\map(
		'GET', '/',
		$getFunction = function () {
		}
	);
	Assert::same(\phi\routes()->GET, [['/', $getFunction]]);
}

{// handle POST request method
	\phi\map(
		'POST', '/',
		$postFunction = function () {
		}
	);
	Assert::same(\phi\routes()->POST, [['/', $postFunction]]);
}

{ // handle 404 errors
	\phi\map(
		404,
		$errorFunction = function () {
		}
	);

	Assert::same(\phi\routes()->error[404], $errorFunction);
}

{ // handle 500 errors
	\phi\map(
		500,
		$errorFunction = function () {

		}
	);
	Assert::same(\phi\routes()->error[500], $errorFunction);
}