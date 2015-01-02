<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/phi.php';


try {
	\phi\map();
	Assert::fail('Not \BadFunctionCallException in map() function');
} catch (\BadFunctionCallException $err) {

}

try {

	\phi\map(new Test);
} catch (\BadFunctionCallException $err) {

}

