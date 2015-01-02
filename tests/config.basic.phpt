<?php
/**
 * @author Roman Ozana <ozana@omdesign.cz>
 */
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/phi.php';

{ // init config by default value and config file
	\phi\config(
		[
			'default' => 'value',
			'overwrite' => false
		],
		[
			'overwrite' => true,
			'annonymous' => function () {
				return "yes it's working";
			}
		]
	);
	Assert::equal('value', \phi\config()->default);
	Assert::true(\phi\config()->overwrite);
}

{ // change some value
	\phi\config()->default = 'set value';
	\phi\config()->overwrite = 'yes';
	Assert::equal('set value', \phi\config()->default);
	Assert::equal('yes', \phi\config()->overwrite);
}

{ // add new value
	\phi\config()->new_key = 'set value';
	Assert::equal('set value', \phi\config()->new_key);
}
