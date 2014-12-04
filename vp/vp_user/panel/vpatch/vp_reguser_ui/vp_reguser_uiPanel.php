<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_reguser_uiPanel extends Panel
	{
		private $mode;
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('reguser_ui');
			$this->mode = 0;
		}

		public function loadTemplate()
		{
			if($this->mode == 0)
				$this->includeTemplate("template/main.php");
			else
				$this->includeTemplate("template/successful.php");
		}

		public function initialize($params = null)
		{
			if(isset($_GET['urerr'])) {
				if($_GET['urerr'] == 0)
					$this->mode = 1;
				else
					$this->addTParam("error", $_GET['urerr']);
			}
			$this->fallback();
			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
					

				}
			}
		}

		public function fallback()
		{
			$this->addFallbackLink('mediag',SystemConfig::appRelativePath("library/media/stdint/general"));

			$spath = SystemConfig::appRelativePath(Managers::AppConfig()->setting('submitrequest'));
			$aid = Session::get('aid');
			$area = Managers::ResourceManager()->getHandlerRef($aid);
			$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/1&dbm-redirect=1";
			$this->addFallbackLink('submit', $qstr);
		}

		public function setAssets()
		{
			$this->addAsset('css', "template/regstyle.css");
		}
	}
?>
