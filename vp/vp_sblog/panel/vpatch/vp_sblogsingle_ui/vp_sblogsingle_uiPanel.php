<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_sblogsingle_uiPanel extends Panel
	{
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_sblogsingle_ui');
		}

		public function loadTemplate()
		{
			$this->includeTemplate("template/main.php");
		}

		public function initialize($params = null)
		{
			$vars = $this->argVar(array('vbbp' => 'vbpost'), null);
			$this->addComponentRequest(3, $vars->__get(null));
			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
				case 3:
					$this->addTParam('post', $rs['result']);
				break;
				}
			}
		}

		public function setAssets()
		{
			$this->addAsset('css', "template/blogsingle_style.css");
		}
	}
?>
