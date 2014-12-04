<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_credmanComponent extends Component
	{
		public function initialize()
		{

		}

		public function createInstance($params = null)
		{
			$rids = Managers::ResourceManager()->queryAssoc("Instance()<Component('vp_credman');");
			if(!$rids)
				return 1;

			return sizeof($rids)+1;
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel)
			{
			case 1:
				$response = $this->checkCredentials($args);
			break;

			case 2:
				$response = $this->logout($args);
			break;

			case 3:
				$response = $this->cuser($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		private function checkCredentials($args)
		{
			$vars = $this->argVar(array('uname' => 'username',
						    'upass' => 'password'), $_POST);

			if($vars->username == null || $vars->password == null) {
				$this->addTrackerParam('cred', 104);
				return 104;
			}

			include_once(LibLoad::shared('vpatch', 'users'));
			$userslib = new usersLibrary($this->db);
			$details = null;
			if(is_numeric($vars->username))
				$details = $userslib->getAccount($vars->username);
			else
				$details = $userslib->getAccountFromUName($vars->username);

			if(!$details) {
				$this->addTrackerParam('cred', 104);
				return 104;
			}
			$details = $details[0];
			$hash = $userslib->generateHash($vars->password, $details['salt']);
			if($hash == $details['hash'])
				$userslib->setUserId($details['id']);
			else {
				$this->addTrackerParam('cred', 104);
				return 104;
			}
			
			$url = "http://".SystemConfig::appServerRoot("index.php?loc=home");
			HttpHeader::Redirect($url);
			$this->addTrackerParam('cred', 102);
			return 102;
		}

		private function logout($args)
		{
			include_once(LibLoad::shared('vpatch', 'users'));
			$userslib = new usersLibrary($this->db);
			$userslib->logout();
			$uid = Session::get('uid');
		}

		private function cuser($args)
		{
			include_once(LibLoad::shared('vpatch', 'users'));
			$userslib = new usersLibrary($this->db);
			$uid = Session::get('uid');
			$details = $userslib->getAccount($uid);
			if(!$details)
				return 104;
			
			return $details[0][1];
		}
	}
?>
