<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class isuserPlugin extends Plugin
	{
		public function init($instance)
		{
			$this->instance = $instance;
		}

		public function process(&$signal)
		{
			$uid = Session::get('uid');
			if($uid == null) {
				$redir = null;
				if(!($redir = $this->getConfig("nuredir")))
					die("Not sure you should be peeking around in here");
				else
					HttpHeader::redirect("?loc=$redir");
			}


			return $signal;
		}
		
		public function getConfigList()
		{
			return array('nuredir');
		}
	}
?>
