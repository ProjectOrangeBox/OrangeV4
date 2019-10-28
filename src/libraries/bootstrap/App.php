<?php

#namespace \; // Global

use projectorangebox\orange\library\exceptions\IO\FileOperationFailedException;
use projectorangebox\orange\library\exceptions\IO\FileNotFoundException;
use projectorangebox\orange\library\exceptions\IO\FileWriteFailedException;
use projectorangebox\orange\library\exceptions\IO\FolderNotWritableException;

class App
{
	/*

	app::path(...) // return complete path based on applications root folder ie. __ROOT__
	app::globr(...) // recursive glob
	app::remove_php_file_from_opcache(...) // remove a opcached PHP file based on it's absolute file path

	"duplicate" many PHP functions to support the applications root folder

	app::file_get_contents(...)
	app::pathinfo(...)
	app::readfile(...)
	app::file(...)
	app::file_exists(...)
	app::file_put_contents(...)
	app::fopen(...)
	app::glob(...)
	app::include(...)
	app::mkdir(...)
	app::parse_ini_file(...)
	app::rename(...)
	app::unlink(...)

	*/

	/* Add Root if it's not already there (ie. glob function array will already have it no need to have you strip it) */
	static public function path(string $path,bool $throw = false): string
	{
		$path = (substr($path,0,strlen(__ROOT__)) != __ROOT__) ? __ROOT__.'/'.\trim($path,'/') : \rtrim($path,'/');

		if ($throw && !\file_exists($path)) {
			throw new FileNotFoundException($path);
		}

		return $path;
	}

	/* read */

	static public function globr(string $pattern,int $flags = 0): array
	{
		return self::_globr(self::path($pattern),$flags);
	}

	static public function glob(string $pattern,int $flags = 0): array
	{
		return \glob(self::path($pattern),$flags);
	}

	static public function file_get_contents(string $filename,bool $throw = true): string
	{
		return \file_get_contents(self::path($filename,$throw));
	}

	static public function pathinfo(string $path, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME,bool $throw = true)
	{
		return \pathinfo(self::path($path,$throw),$options);
	}

	static public function readfile(string $filename,bool $throw = true): int
	{
		return \readfile(self::path($filename,$throw));
	}

	static public function include(string $filename,bool $throw = true)
	{
		return include self::path($filename,$throw);
	}

	static public function parse_ini_file(string $filename, bool $process_sections = FALSE, int $scanner_mode = INI_SCANNER_NORMAL,bool $throw = true): array
	{
		return \parse_ini_file(self::path($filename,$throw),$process_sections,$scanner_mode);
	}

	static public function file_exists(string $filename,bool $throw = false): bool
	{
		return \file_exists(self::path($filename,$throw));
	}

	static public function file(string $filename,int $flags = 0,bool $throw = false): array
	{
		return \file(self::path($filename,$throw),$flags);
	}

	static public function fopen(string $filename, string $mode,bool $throw = false)
	{
		return \fopen(self::path($filename,$throw),$mode);
	}

	/* write */

	static public function file_put_contents(string $filepath, $content): int
	{
		$filepath = self::path($filepath);

		/* get the path where you want to save this file so we can put our file in the same file */
		$dirname = \dirname($filepath);

		/* is the directory writeable */
		if (!is_writable($dirname)) {
			throw new FolderNotWritableException($dirname);
		}

		/* create file with unique file name with prefix */
		$tmpfname = \tempnam($dirname, 'afpc_');

		/* did we get a temporary filename */
		if ($tmpfname === false) {
			throw new FileWriteFailedException($tmpfname);
		}

		/* write to the temporary file */
		$bytes = \file_put_contents($tmpfname, $content);

		/* did we write anything? */
		if ($bytes === false) {
			throw new FileWriteFailedException($bytes);
		}

		/* changes file permissions so I can read/write and everyone else read */
		if (\chmod($tmpfname, 0644) === false) {
			throw new FileOperationFailedException($tmpfname);
		}

		/* move it into place - this is the atomic function */
		if (\rename($tmpfname, $filepath) === false) {
			throw new FileOperationFailedException($tmpfname.' > '.$filepath);
		}

		/* if it's cached we need to flush it out so the old one isn't loaded */
		self::remove_php_file_from_opcache($filepath);

		/* return the number of bytes written */
		return $bytes;
	}

	static public function unlink(string $filename,bool $throw = false): bool
	{
		return unlink(self::path($filename,$throw));
	}

	static public function mkdir(string $pathname,int $mode = 0777,bool $recursive = true): bool
	{
		$pathname = self::path($pathname);

		if (!\file_exists($pathname)) {
			$umask = \umask(0);
			$bool = \mkdir($pathname, $mode, $recursive);
			\umask($umask);
		} else {
			$bool = true;
		}

		return $bool;
	}

	static public function rename(string $oldname,string $newname,bool $throw = true): bool
	{
		return \rename(self::path($oldname,$throw),self::path($newname));
	}

	/* protected */

	static protected function remove_php_file_from_opcache(string $filepath) : bool
	{
		$success = true;

		/* flush from the cache */
		if (\function_exists('opcache_invalidate')) {
			$success = \opcache_invalidate($filepath, true);
		} elseif (\function_exists('apc_delete_file')) {
			$success = \apc_delete_file($filepath);
		}

		return $success;
	}

	static protected function _globr(string $pattern,int $flags = 0): array
	{
		$files = \glob($pattern, $flags);

		foreach (\glob(\dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = \array_merge($files, self::_globr($dir.'/'.\basename($pattern), $flags));
		}

		return $files;
	}

} /* end app */