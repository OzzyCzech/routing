<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/phi.php';

{ // overwrite by own function and add default value

	function config() {
		return \phi\config(['value' => false], ['value' => true]);
	}

	Assert::true(config()->value);

	// try overwrite again
	\phi\config(['value' => false]);
	Assert::true(config()->value);

}
