<?php

	function adminer_object()
	{
		$aLoad = [];

		require "plugins/CSqlite.php";
		$aLoad[] = new CSqlite();

		class AdminerCustomization extends Adminer\Plugins
		{
		}
		return new AdminerCustomization($aLoad);
	}
	include "./adminer.php";