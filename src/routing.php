<?php
/** @author Roman Ozana <ozana@omdesign.cz> */


namespace route {

	use function app\routes as routes;

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
				throw new \BadFunctionCallException('Invalid number of arguments.', 500);
		}
	}

	function error() {
		$argc = func_num_args();
		$argv = func_get_args();
		if (!$argc) {
			throw new \BadFunctionCallException('Invalid number of arguments.', 500);
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
}

namespace app {

	function routes() {
		static $routes;
		return (!$routes) ? $routes = (object)['any' => [], 'all' => [], 'error' => []] : $routes;
	}

	function dispatch() {
		$argv = func_get_args();
		$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
		$method = \request\method();

		$pattern = null;
		$func = null;
		$vals = null;

		// getting all maps
		$maps = (array)routes()->any;
		if (isset(routes()->{$method})) {
			$maps = array_merge((array)routes()->{$method}, $maps);
		}

		// iterate over all maps
		foreach ($maps as $temp) {
			list($pattern, $callback) = $temp;

			$pattern = trim($pattern, '/');
			$pattern = preg_replace(
				[
					'@<([^:]+)>@U', # <param> => <param>[^/]+
					'@<([^:]+)(:(.+))?>@U', # <param:...> => (?<param>...)
				],
				[
					'<$1:[^/]+>',
					'(?<$1>$3)',
				],
				$pattern
			);

			// match current path with any maps callback
			if (preg_match('@^' . $pattern . '$@', $path, $vals)) {
				$func = $callback;
				break;
			}
		}

		// valid handler, try to parse out route symbol values
		if ($func && is_callable($func)) {

			array_shift($vals); // remove top group from vals
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
				$argv = array_merge(['method' => $method, 'path' => $path], $argv);
				return call_user_func_array(routes()->all, $argv);
			} else {
				$func = '\route\error';
				array_unshift($argv, 404);
			}
		}

		return call_user_func_array($func, $argv);
	}
}

namespace request {

	function method() {
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		if ($method === 'POST') {
			if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
				$method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
			} else {
				$method = isset($_POST['_method']) ? strtoupper($_POST['_method']) : $method;
			}
		}
		return $method;
	}

}