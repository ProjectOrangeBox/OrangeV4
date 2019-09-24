<?php

/**
 * Wrapper for getting configure with dot notation from the orange config class
 * ci('config')->dot_item(...)
 *
 * @param string $setting
 * @param mixed $default
 *
 * @throws \Exception
 *
 * @return mixed
 *
 * #### Example
 * ```
 * $foo = config('file.key');
 * $foo = config('file.key2','default value');
 * ```
 */
if (!function_exists('config')) {
	function config(string $arg1,/* mixed */ $arg2 = '') /* mixed */
	{
		return ci('config')->item($arg1, $arg2);
	}
}

/**
 * Wrapper for validation filters
 * This returns the filtered value
 *
 */
if (!function_exists('filter')) {
	function filter($input, string $rules) /* mixed */
	{
		/* passed by reference */
		return ci('validate')->filter($input, $rules);
	}
}

/**
 * Wrapper for validate single
 * This return whether there validation
 * passes (true)
 * or fails (false)
 *
 */
if (!function_exists('valid')) {
	function valid($input, string $rules): bool
	{
		return ci('validate')->variable($input, $rules);
	}
}

/**
 * Escape any single quotes with \"
 *
 * @param string $string
 *
 * @return string
 *
 */
if (!function_exists('esc')) {
	function esc(string $string): string
	{
		return str_replace('"', '\"', $string);
	}
}

/**
 * Escape html special characters
 *
 * @param $string
 *
 * @return string
 *
 */
if (!function_exists('e')) {
	function e(string $input): string
	{
		return (empty($input)) ? '' : html_escape($input);
	}
}

/**
 * End the current session and store session data.
 * (7.2 returns a boolean but prior it was null)
 * therefore we don't return anything
 *
 * @return void
 *
 */
if (!function_exists('unlockSession')) {
	function unlockSession(): void
	{
		session_write_close();
	}
}

/* load the config file */

if (!function_exists('loadFileConfig')) {
	function loadFileConfig(string $filename, bool $throwException = true, string $variableVariable = 'config'): array
	{
		return ci('servicelocator')->loadFileConfig($filename,$throwException,$variableVariable);
	}
}

/* services */

if (!function_exists('findService')) {
	function findService(string $serviceName, bool $throwException = true, string $prefix = '') /* mixed false or string */
	{
		return ci('servicelocator')->findService($serviceName,$throwException,$prefix);
	}
}
