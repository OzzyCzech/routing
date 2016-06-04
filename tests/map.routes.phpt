<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';

{ // check exception if map getting zero arguments
	Assert::exception('\route\map', '\BadFunctionCallException', 'Invalid number of arguments.', 500);
}

{ // handle all request method (failover for 404 requests)
	\route\map(
		$allFunction = function () {
		}
	);
	Assert::same(\app\routes()->all, $allFunction);
}

{ // handle any request method
	\route\map(
		'/', $anyFunction = function () {
	}
	);
	Assert::same(\app\routes()->any, [['/', $anyFunction]]);
}

{ // handle GET request method
	\route\map(
		'GET', '/', $getFunction = function () {
	}
	);
	Assert::same(\app\routes()->GET, [['/', $getFunction]]);
}

{// handle POST request method
	\route\map(
		'POST', '/', $postFunction = function () {
	}
	);
	Assert::same(\app\routes()->POST, [['/', $postFunction]]);
}

{ // handle 404 errors
	\route\map(
		404, $errorFunction = function () {
	}
	);
	Assert::same(\app\routes()->error[404], $errorFunction);
}

{ // handle 500 errors
	\route\map(
		500,
		$errorFunction = function () {
		}
	);
	Assert::same(\app\routes()->error[500], $errorFunction);
}