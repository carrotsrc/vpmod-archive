<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_blogman_uiPanel extends Panel
	{
		private $mode;
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_blogman_ui');
			$this->mode = 1;
		}

		public function loadTemplate()
		{
			$this->fallback();
			switch($this->mode) {
			case 0:
				$this->includeTemplate("template/newBlog.php");
			break;

			case 1;
				$this->includeTemplate("template/main.php");
			break;

			case 2:
				$this->includeTemplate("template/manageBlog.php");
			break;

			case 3:
				$this->includeTemplate("template/newPost.php");
			break;

			case 4:
				$this->includeTemplate("template/editPost.php");
			break;
			}
		}

		public function initialize($params = null)
		{
			$vpid = 0;
			if(isset($_GET['vpbbid'])) {
				$vpid = $_GET['vpbbid'];
				$this->addTParam('vpid', $vpid);
				if($_GET['vpbbid'] == 0)
					$this->mode = 0;
				else
					$this->mode = 2;
			}
			
			$this->addComponentRequest(11, 101);

			if($this->mode == 2) {
				if(isset($_GET['vpba'])) {
					if($_GET['vpba'] == 1)
						$this->mode = 3;
					else
					if($_GET['vpba'] == 2) {
						$this->mode = 4;
						$pid = $_GET['vpbpi'];
						$this->addTParam('post_id', $pid);
						$this->addComponentRequest(2, array(
										'vpbb' => $vpid,
										'vpbi' => $pid
									));
					}
				}
				else {
					$this->addComponentRequest(1, array(
								'vpbb' => $vpid
								));
					$this->addComponentRequest(21, array(
										'vpbb' => $vpid,
									));
					if(isset($_GET['vpbv']))
						$this->addTParam('instman', true);
					else
						$this->addTParam('instman', false);
				}

			}
			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
				case 1:
					if($rs['result'] == 104)
						$this->addTParam('posts', array());
					else
						$this->addTParam('posts', $rs['result']);
				break;

				case 2:
					if($rs['result'] == 104)
						break;

					$this->addTParam('post', $rs['result'][0]);
				break;
				case 11:
					$this->addTParam('blogs', $rs['result']);
				break;
				case 21:
					$this->addTParam('instances', $rs['result']);
				break;
				}
			}
		}

		public function setAssets()
		{
			$this->addAsset('css', '/.assets/general.css');
		}

		private function fallback()
		{
			$qstr = QStringModifier::modifyParams(array('vpbbid' => null));
			//$qstr = SystemConfig::appServerRoot($qstr);
			$this->addFallbackLink('change', $qstr);

			$qstr = QStringModifier::modifyParams(array('vpba' => null));
			$this->addFallbackLink('newpost', $qstr);

			$qstr = QStringModifier::modifyParams(array('vpba' => 2));
			$this->addFallbackLink('edit', $qstr);

			$qstr = QStringModifier::modifyParams(array('vpbv' => null));
			$this->addFallbackLink('chmd', $qstr);

			$spath = SystemConfig::appRelativePath(Managers::AppConfig()->setting('submitrequest'));
			$aid = Session::get('aid');
			$area = Managers::ResourceManager()->getHandlerRef($aid);

			$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/20&dbm-redirect=1";
			$this->addFallbackLink('addview', $qstr);

			if($this->mode == 0) {
				$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/10&dbm-redirect=1";
				$this->addFallbackLink('add', $qstr);
			}

			if($this->mode == 3) {
				$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/3&dbm-redirect=1";
				$this->addFallbackLink('add', $qstr);
			}

			if($this->mode == 4) {
				$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/4&dbm-redirect=1";
				$this->addFallbackLink('update', $qstr);
			}

		}
	}
?>
