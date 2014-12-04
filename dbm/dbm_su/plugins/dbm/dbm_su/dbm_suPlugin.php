<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
 	/*
	*  This plugin switches the user ID
	*  Obviously do not put in a live system.
	*  If the debug flag is not set, it will ignore
	*/
	class dbm_suPlugin extends Plugin
	{
		public function init($instance)
		{
			$this->instance = $instance;
		}

		public function process(&$signal)
		{
			if(!(SystemConfig::$KS_FLAG & KS_DEBUG_MICRO))
				return $signal;

			if(isset($_GET['dbmsu']))
				Session::set('uid', $_GET['dbmsu']);
			return $signal;
		}

	}
?>
