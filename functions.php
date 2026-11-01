<?php

/**
 * This file contains functions that may be called directly without a WordPress hook.
 */

/**
 * Get current plugin's metadata.
 *
 * @return	array	Plugin data listed below (*Values will be empty if not supplied by the plugin*):
 * - `Name`        => *(string)* Name of the plugin. Should be unique.
 * - `Title`       => *(string)* Title of the plugin and link to the plugin's site (if set).
 * - `Description` => *(string)* Plugin description.
 * - `Author`      => *(string)* Author's name.
 * - `AuthorURI`   => *(string)* Author's website address (if set).
 * - `Version`     => *(string)* Plugin version.
 * - `TextDomain`  => *(string)* Plugin textdomain.
 * - `DomainPath`  => *(string)* Plugins relative directory path to .mo files.
 * - `Network`     => *(bool)* Whether the plugin can only be activated network-wide.
 * - `RequiresWP`  => *(string)* Minimum required version of WordPress.
 * - `RequiresPHP` => *(string)* Minimum required version of PHP.
 * - `UpdateURI`   => *(string)* ID of the plugin for update purposes, should be a URI.
 */
function fdtbwpbGetPluginData(): array
{
	if (!is_admin()) return [];
	if (!function_exists('get_plugin_data')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return get_plugin_data(FDTBWPB_FILE);
}

/**
 * Finds files recursively matching a glob pattern in all subdirectories.
 *
 * @param	string	$pattern	Glob pattern to match.
 * @param	int		$flags		Optional glob flags. (Does not support `GLOB_BRACE`)
 *
 * @return	array				List of matched file paths, or an empty array if no files matched.
 */
function fdtbwpbGlobRecursive(string $pattern, int $flags = 0): array
{
	$files = glob($pattern, $flags);
	foreach (glob(dirname($pattern) . '/*', \GLOB_ONLYDIR | \GLOB_NOSORT) as $dir)
		$files = array_merge($files, fdtbwpbGlobRecursive($dir . '/' . basename($pattern), $flags));
	return $files;
}

/**
 * Converts a file path to a relative namespace.
 *
 * @param	string	$file		File path to convert.
 * @param	string	$baseDir	Base directory to make the path relative to.
 *
 * @return	string				Relative namespace, or an empty string if parameters are empty.
 * @source	http://PHP.net/manual/en/function.glob.php#106595
 */
function fdtbwpbConvertFilePathToRelativeNamespace(string $file, string $baseDir): string
{
	if (empty($file) || empty($baseDir)) return '';

	$relativeNamespace = str_replace($baseDir . '/', '', $file);
	$relativeNamespace = str_replace('/', '\\', $relativeNamespace);
	$relativeNamespace = str_replace('.php', '', $relativeNamespace);

	return $relativeNamespace;
}
