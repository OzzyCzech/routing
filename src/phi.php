<?php
namespace phi;

	/**
	 * @author Roman Ozana <ozana@omdesign.cz>
	 */

/**
 * Basic config function.
 *
 * @return object
 */
function config() {
	static $config;
	return $config ? $config : $config = (object)call_user_func_array('\array_replace_recursive', func_get_args());
}

/**
 * @return object
 */
function routes() {
	static $routes;
	return (!$routes) ? $routes = (object)['any' => [], 'all' => [], 'error' => []] : $routes;
}

/**
 * Function for mapping actions to routes.
 */
function map() {
	$argv = func_get_args();

	// try to figure out how we were called
	switch (count($argv)) {
		// complete params (method, path, handler)
		case 3:
			foreach ((array)$argv[0] as $verb)
				routes()->{strtoupper($verb)}[] = ['/' . trim($argv[1], '/'), $argv[2]];
			break;
		// either (path, handler) or (code, handler)
		case 2:
			$argv[0] = (array)$argv[0];
			if (ctype_digit($argv[0][0])) {
				foreach ($argv[0] as $code)
					routes()->error[$code] = $argv[1];
			} else {
				foreach ($argv[0] as $path)
					routes()->any[] = ['/' . trim($path, '/'), $argv[1]];
			}
			break;
		// any method and any path (just one for this, replace ref)
		case 1:
			routes()->all = $argv[0];
			break;
		// everything else
		default:
			throw new \BadFunctionCallException(
				'Invalid number of arguments.',
				500
			);
	}
}

/**
 * Handling all errors.
 *
 * @return mixed
 * @throws \BadFunctionCallException
 */
function error() {
	$argc = func_num_args();
	$argv = func_get_args();
	if (!$argc) {
		throw new \BadFunctionCallException(
			'Invalid number of arguments.',
			500
		);
	}
	$code = $argv[0];
	$func = (
	isset(routes()->error[$code]) ?
		routes()->error[$code] :
		function ($code) {
			return http_response_code($code);
		}
	);
	http_response_code($code);
	return call_user_func_array($func, $argv);
}

/**
 * Dispatch current request.
 *
 * @return mixed
 */
function dispatch() {
	$argv = func_get_args();
	$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$method = strtoupper($_SERVER['REQUEST_METHOD']);

	// override POST method
	if ($method === 'POST') {
		if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
			$method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
		} else {
			$method = isset($_POST['_method']) ? strtoupper($_POST['_method']) : $method;
		}
	}

	$rexp = null;
	$func = null;
	$vals = null;

	// replace named params (support regex formats)
	$rxcb = function ($matches) {
		if (isset($matches[3])) {
			return "(?<{$matches[1]}>{$matches[3]})";
		}
		return "(?<{$matches[1]}>[^/]+)";
	};


	// getting all maps
	$maps = (array)routes()->any;
	if (isset(routes()->{$method})) {
		$maps = array_merge((array)routes()->{$method}, $maps);
	}

	foreach ($maps as $temp) {
		list($rexp, $call) = $temp;
		$rexp = trim($rexp, '/');
		$rexp = preg_replace_callback('@\{([^:]+)(:(.+))?\}@', $rxcb, $rexp);
		if (!preg_match('@^' . $rexp . '$@', $path, $vals)) {
			continue;
		}
		$func = $call;
		break;
	}

	// valid handler, try to parse out route symbol values
	if ($func && is_callable($func)) {
		// remove top group from vals
		array_shift($vals);
		// extract route symbols and run the hook()s
		if ($vals) {
			// extract any route symbol values
			$toks = array_filter(array_keys($vals), 'is_string');
			$vals = array_map(
				'urldecode',
				array_intersect_key(
					$vals,
					array_flip($toks)
				)
			);

			array_unshift($argv, $vals);
		}
	} else {
		if (is_callable(routes()->all)) {
			$argv = array_merge($argv, ['method' => $method, 'path' => $path]);
			return call_user_func_array(routes()->all, $argv);
		} else {
			$func = 'error';
			array_unshift($argv, 404);
		}
	}

	return call_user_func_array($func, $argv);
}