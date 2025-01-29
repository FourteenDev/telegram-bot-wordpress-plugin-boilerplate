<?php

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
function fdtbwpb_get_plugin_data()
{
	if (!is_admin()) return [];
	if (!function_exists('get_plugin_data')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return get_plugin_data(FDTBWPB_FILE);
}
