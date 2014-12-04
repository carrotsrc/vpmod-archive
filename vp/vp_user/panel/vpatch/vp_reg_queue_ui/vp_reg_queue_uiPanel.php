<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_reg_queue_uiPanel extends Panel
	{
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_reg_queue_ui');
		}

		public function loadTemplate()
		{
			$qstr = QStringModifier::modifyParams(array('rqkey' => null));
			$this->addFallbackLink('activate', $qstr);
			$this->includeTemplate("template/main.php");
		}

		public function initialize($params = null)
		{
			if(isset($_GET['rqkey']))
				$this->addComponentRequest(2, array('rqkey' => $_GET['rqkey']));

			$this->addComponentRequest(1, 101);

			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
				case 1:
					$this->addTParam('queue', $rs['result']);
				break;
				}
			}
		}

		public function setAssets()
		{
			$this->addAsset('css', '/.assets/general.css');
			$this->addAsset('css', 'template/rqtable.css');
		}
	}
?>
