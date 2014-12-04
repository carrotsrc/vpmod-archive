<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_logoutPlugin extends Plugin
	{
		public function init($instance)
		{
			$this->instance = $instance;
		}

		public function process(&$signal)
		{
			Session::destroy();
			$redir = $this->getConfig("redir");

			if($redir == null)
				$redir = "?loc=web";
			else
				$redir = "?loc=$redir";

			HttpHeader::redirect($redir);
			return $signal;
		}

		public function getConfigList()
		{
			return array("redir");
		}

	}
?>
