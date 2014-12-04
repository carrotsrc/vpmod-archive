<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_reg_queueComponent extends Component
	{
		private $rlib;
		private $resManager;
		public function initialize()
		{
			include_once(LibLoad::shared('vpatch', 'reguser'));
			$this->rlib = new reguserLibrary($this->db);
			$this->resManager = Managers::ResourceManager();
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->getQueue($args);
			break;

			case 2:
				$response = $this->activate($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		private function getQueue($args)
		{
			return $this->rlib->getRegistrationQueue();
		}

		private function activate($args)
		{
			if(($uid = $this->rlib->activateUser($args['rqkey']))) {
				$rid = $this->resManager->addResource("User", $uid, $this->rlib->getUsername());
				$this->rlib->removeKeyFromQueue($args['rqkey']);
				$this->setRio(RIO_INS, $rid);
			}
		}

	}
?>
