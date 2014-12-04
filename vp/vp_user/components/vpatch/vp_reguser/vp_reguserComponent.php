<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_reguserComponent extends Component
	{
		private $resManager;
		public function initialize()
		{

		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->registerStageOne($args);
			break;

			case 2:
				$response = $this->registerStageTwo($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		public function registerStageOne($args)
		{
			$vars = $this->argVar( array(
						'uruser' => 'user',
						'urpass' => 'pass',
						'uremail' => 'email'
					), $_POST);
			
			include_once(LibLoad::shared('vpatch', 'reguser'));
			$rlib = new reguserLibrary($this->db);
			if(
			$vars->user == null || $vars->user == "" ||
			$vars->pass == null || $vars->pass == "" ||
			$vars->email == null || $vars->email == ""
			) {
				$this->addTrackerParam('urerr', '1');
				return 104;
			}

			$parts = explode("@", $vars->email);

			if(sizeof($parts) < 2) {
				$this->addTrackerParam('urerr', '2');
				return 104;
			}

			$doms = $this->getConfig('lockdom');
			if($doms) {
				$doms = explode(';', $doms);
				$access = false;
				$udom = strtolower($parts[1]);
				foreach($doms as $cdom) {
					if($udom == $cdom) {
						$access = true;
						break;
					}
				}

				if(!$access) {
					$this->addTrackerParam('urerr', '2');
					return 104;
				}
			}
			if(!$this->getConfig("instreg")) {
				// add to the registratiom queue
				if(!$rlib->addUser($vars->user, $vars->pass, $vars->email)) {
					$this->addTrackerParam('urerr', '3');
					return 104;
				}
				$this->addTrackerParam('urerr', '0');
				$rlib->mailKey($vars->user);
			} else {
				// instantly register
				$this->resManager = Managers::ResourceManager();
				$ulib = LibLoad::obj('vpatch', 'users', $this->db);
				$hash = $ulib->generateHash($vars->pass);
				$salt = $ulib->getSalt();
				$ref = 0;
				if(!($ref = $ulib->addAccount($vars->user, $hash, $salt, $vars->email))) {
					$this->addTrackerParam('urerr', '3');
					return 104;
				}

				$this->addTrackerParam('urerr', '0');
				$this->resManager->addResource('User', $ref, $vars->user);
			}

			return 102;
		}

		private function registerStagetwo($args)
		{
			$this->resManager = Managers::ResourceManager();
			$vars = $this->argVar( array(
						'urkey' => 'key',
					), $args);
			
			include_once(LibLoad::shared('vpatch', 'reguser'));
			$rlib = new reguserLibrary($this->db);

			if($vars->key == null)
				return 104;

			$uid = $rlib->activateUser($vars->key);
			if($uid === false)
				return 104;

			$chk = $rlib->removeKeyFromQueue($vars->key);
			$id = $this->resManager->addResource("User", $uid, $rlib->getUsername());
			$this->setRio(RIO_INS, $id);
			return 102;
		}

		public function getConfigList()
		{
			if(!$this->maintainReady())
				return null;

			return array('lockdom', 'instreg');
		}

	}
?>
