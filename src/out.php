<?php

/**
 * Try download file.
 *
 * @param $file
 * @param null $name
 * @param null $expire
 */
function download($file, $name = null, $expire = null) {

	($finf = finfo_open(FILEINFO_MIME)) or error(500, "download() failed to open fileinfo database");
	$mime = finfo_file($finf, $file);
	finfo_close($finf);

	// cache headers
	header('Pragma: public');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
	header('ETag: ' . md5(dirname($file)));

	// if we want this to persist
	if ($expire > 0) {
		header('Cache-Control: maxage=' . $expire);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
	}

	header('Content-Disposition: attachment; filename=' . urlencode($name ?: basename($file)));
	header('Content-Type: ' . $mime);
	header('Content-Length: ' . filesize($file));
	header('Connection: close');

	readfile($file);
}


/**
 * Return file MIME type info
 *
 * @param string $file
 * @return string
 */
function mime($file) {
	if (!$finf = finfo_open(FILEINFO_MIME)) throw new \RuntimeException('');
	$mime = finfo_file($finf, $file);
	finfo_close($finf);
	return $mime;
}

/**
 * Return current URL.
 *
 * @param  null|string $slug
 * @return string
 */
function url($slug = null) {
	$server = (isset($_SERVER['HTTPS']) && strcasecmp(
			$_SERVER['HTTPS'], 'off'
		) ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :
			(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '')) .
		($_SERVER['SERVER_PORT'] == '80' ? null : ':' . $_SERVER['SERVER_PORT']);

	return $server . '/' . ltrim(parse_url($slug, PHP_URL_PATH), '/');
	//return filter('url', $server . '/' . ltrim(parse_url($slug, PHP_URL_PATH), '/'), $slug, $server);
}

/**
 * Prints out no-cache headers.
 */
function nocache() {
	header('Expires: Tue, 13 Mar 1979 18:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME']) . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
}


/**
 * Shortcut for http_response_code().
 *
 * @param $code
 * @return int
 */
function status($code) {
	return http_response_code($code);
}

/**
 * Shortcut for dumping a redirect header (no longer exits).
 *
 * @param $path
 * @param int $code
 * @param bool $halt
 */
function redirect($path, $code = 302, $halt = false) {
	header("Location: {$path}", true, $code);
	$halt && exit;
}

/**
 * Maps directly to json_encode, but renders JSON headers as well.
 */
function json() {
	$json = call_user_func_array('json_encode', func_get_args());
	$err = json_last_error();
	// trigger a user error for failed encodings
	if ($err !== JSON_ERROR_NONE) {
		throw new \RuntimeException(
			"JSON encoding failed [{$err}].",
			500
		);
	}
	header('Content-type: application/json');
	return print $json;
}

/**
 * Accessor for $_SESSION
 *
 * @param $name
 * @param null $value
 * @return null
 */
function session($name, $value = null) {
	if (func_num_args() == 2) {
		return ($_SESSION[$name] = $value);
	}
	return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
}

/**
 * @return mixed|null
 */
function cookies() {
	$argc = func_num_args();
	$argv = func_get_args();
	if ($argc == 1) {
		return isset($_COOKIE[$argv[0]]) ? $_COOKIE[$argv[0]] : null;
	}
	return call_user_func_array('setcookie', $argv);
}

/**
 * Returns the best-guess remote address.
 *
 * @return string
 */
function ip() {
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	}
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	return $_SERVER['REMOTE_ADDR'];
}

/**
 * In-request store for values
 */
function stash($name = null, $value = null) {
	static $data;
	$argc = func_num_args();
	# value fetch
	if ($argc === 1) {
		return isset($data[$name]) ? $data[$name] : null;
	}
	# stash reset
	if ($argc === 0) {
		return ($data = []);
	}
	# value assignment
	return ($data[$name] = $value);
}


/**
 * Returns the value for an http request header, or sets an http
 * response header (maps to php's header function)
 */
function headers() {
	static $headers = null;

	$argc = func_num_args();
	$argv = func_get_args();

	// error case
	if ($argc < 1) {
		throw new BadFunctionCallException(
			'Invalid number of arguments.',
			500
		);
	}
	// fetch case
	if ($argc === 1) {
		// first call, prime it
		if (!count($headers)) {
			// if we're not in CLI, use it
			if (function_exists('getallheaders')) {
				$headers = array_change_key_case(getallheaders());
			} else {
				// manual header extraction (CLI + test)
				$special = ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'];
				// get the rest of the headers out
				foreach ($_SERVER as $name => $data) {
					if (0 === strpos($name, 'HTTP_')) {
						$name = strtolower(str_replace('_', '-', substr($name, 5)));
						$headers[$name] = $data;
					} else if (in_array($name, $special)) {
						$name = strtolower(str_replace('_', '-', $name));
						$headers[$name] = $data;
					}
				}
			}
		}
	}
}

/**
 * Aaccessor for $_FILES, also consolidates array file uploads, but when using
 * the files, be sure to use either is_uploaded_file() or move_uploaded_file()
 * to ensure validity of file targets
 *
 * @param $name
 * @return array|null
 */
function attachments($name) {
	static $cache = [];
	// return cached copy
	if (isset($cache[$name])) {
		return $cache[$name];
	}
	if (!isset($_FILES[$name])) {
		return null;
	}
	// single-file attachment (no need to cache)
	if (!is_array($_FILES[$name]['name'])) {
		return $_FILES[$name];
	}
	// attachment is an array
	$result = [];
	// consolidate file info
	foreach ($_FILES[$name] as $k1 => $v1)
		foreach ($v1 as $k2 => $v2)
			$result[$k2][$k1] = $v2;
	// cache and return array uploads
	return ($cache[$name] = $result);
}

/**
 * Read request RAW body.
 *
 * @param bool $load
 * @param string $pipe
 * @return array
 */
function input($load = false, $pipe = 'php://input') {
	static $cache = null;
	# if called before, just return previous data
	if ($cache) {
		return $cache;
	}
	# do a best guess
	$content_type = (
	isset($_SERVER['HTTP_CONTENT_TYPE']) ?
		$_SERVER['HTTP_CONTENT_TYPE'] :
		$_SERVER['CONTENT_TYPE']
	);
	# try to load everything
	if ($load) {
		$content = file_get_contents($pipe);
		$content_type = preg_split('/ ?; ?/', $content_type);
		# type-content tuple
		return [$content_type, $content];
	}
	# create a temp file with the data
	$path = tempnam(sys_get_temp_dir(), 'disp-');
	$temp = fopen($path, 'w');
	$data = fopen($pipe, 'r');
	stream_copy_to_stream($data, $temp);
	fclose($temp);
	fclose($data);
	# type-path tuple
	return [$content_type, $path];
}

/**
 * @param null $name
 * @param null $default
 * @return array|null
 */
function params($name = null, $default = null) {
	static $source = null;

	// setup source on first call
	if (!$source) {

		// by default, only get values from $_GET and $_POST
		$source = array_merge($_GET, $_POST);

		// if content-type is application/json, merge in values from request_body()
		if (strtolower(headers('content-type')) == 'application/json')
			$source = array_merge($source, input());
	}

	if (is_string($name))
		return (isset($source[$name]) ? $source[$name] : $default);

	if ($name == null)
		return $source;

	// used by on() for merging in route symbols
	if (is_array($name))
		$source = array_merge($source, $name);
}


// ---------------------------------------------------------------------------------------------------------------------

is_file(getcwd() . '/functions.php') ? require_once getcwd() . '/functions.php' : null; // add another functions