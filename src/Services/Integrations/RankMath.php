<?php

namespace TelegramPluginBoilerplate\Services\Integrations;

class RankMath
{
	public function __construct()
	{
		add_filter('rank_math/vars/register_extra_replacements', [$this, 'registerNewVariables']);
	}

	/**
	 * An example empty method to showcase Rank Math integration.
	 *
	 * @return	void
	 *
	 * @hooked	action: `rank_math/vars/register_extra_replacements` - 10
	 */
	public function registerNewVariables()
	{
		// Some action
	}
}
