<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_sblogview_uiPanel extends Panel
	{
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_blogview_ui');
		}

		public function loadTemplate()
		{
			$this->includeTemplate("template/main.php");
		}

		public function initialize($params = null)
		{
			$this->addComponentRequest(1, 101);
			$this->addComponentRequest(2, 101);
			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
				case 1:
					if($rs['result'] == 104) {
						$this->addTParam('posts', array());
						break;
					}
					$this->addTParam('posts', $rs['result']);
				break;

				case 2:
					$this->addTParam('blog', $rs['result']);
				break;
				}
			}
		}

		public function setAssets()
		{
			$this->addAsset('css', '/.assets/general.css');
		}
	}
?>
