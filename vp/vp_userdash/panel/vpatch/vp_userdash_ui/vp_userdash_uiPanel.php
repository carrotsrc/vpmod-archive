<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

	class vp_userdash_uiPanel extends Panel
	{
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_userdash_ui');
			$this->jsCommon = "vp_userdashInterface";
		}

		public function loadTemplate()
		{
			$this->includeTemplate("template/main.php");
		}

		public function initialize($params = null)
		{
			$this->addComponentRequest(1, 101);

			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				$res = $rs['result'];
				switch($rs['jack']) {
				case 1:
					if($res == 104)
						break;
					$this->addTParam('users', $res);
				break;
				}
			}
		}

		public function setAssets()
		{
			$this->addAsset('js', "/G/toolset.js");
			$this->addAsset('js', "/G/resbin_sc.js");
			$this->addAsset('js', "template/userdash_sc.js");
			$this->addAsset('css', "template/userdash_style.css");

		}
	}
?>
