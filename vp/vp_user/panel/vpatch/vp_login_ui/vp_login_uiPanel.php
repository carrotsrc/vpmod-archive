<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_login_uiPanel extends Panel
	{
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_login_ui');
		}

		public function loadTemplate()
		{
			$this->includeTemplate('templates/entry.php');
		}

		public function initialize($params = null)
		{
			$vars = $this->argVar(array('cred' => 'cred'), null);

			if($vars->cred == 102) {
				$this->addComponentRequest(3, 101);
				$this->addTParam('successful', true);
			}
			else
			If($vars->cred == 104)
				$this->addTParam('err', true);


			$this->fallback();
			parent::initialize();
		}

		public function applyRequest($result)
		{
			foreach($result as $rs) {
				switch($rs['jack']) {
				case 3:
					if($rs['result'] == 104)
						break;
					$this->addTParam('username', $rs['result']);
				break;
				}
			}
		}

		private function fallback()
		{
			$spath = SystemConfig::appRelativePath(Managers::AppConfig()->setting('submitrequest'));
			$aid = Session::get('aid');
			$area = Managers::ResourceManager()->getHandlerRef($aid);
			$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/1&dbm-redirect=1";
			$this->addFallbackLink('login', $qstr);
		}

		public function setAssets()
		{
			$this->addAsset('css', 'templates/login.css');
		}
	}
?>
