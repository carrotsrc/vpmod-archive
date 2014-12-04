<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_activation_uiPanel extends Panel
	{
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('activation_ui');
		}

		public function loadTemplate()
		{
			$this->includeTemplate("template/main.php");
		}

		public function initialize($params = null)
		{
			$key = null;
			if(isset($_GET['urkey'])) {
				$key = $_GET['urkey'];
				$this->addComponentRequest(2, array(
								'urkey' => $key));
			}

			$this->addTParam('key', $key);
			$this->fallback();
			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
				case 2:
					if($rs['result'] == 102)
						$this->addTParam('activated', true);
					else
						$this->addTParam('activated', false);
				break;
				}
			}
		}

		public function fallback()
		{
			$this->addFallbackLink('mediag',SystemConfig::appRelativePath("library/media/stdint/general"));
		}

		public function setAssets()
		{
			$this->addAsset('css', "template/regstyle.css");
		}
	}
?>
