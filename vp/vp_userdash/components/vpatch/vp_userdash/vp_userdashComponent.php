<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_userdashComponent extends Component
	{
		private $resManager;
		private $plib;
		public function initialize()
		{
			$this->resManager = Managers::ResourceManager();
		}

		public function createInstance($params = null)
		{
			$this->resManager = Managers::ResourceManager();
			$id = 1;
			if(($r = $this->resManager->queryAssoc("Instance()<Component('vp_userdash');"))) {
				$id = sizeof($r[0])+1;
			}

			return $id;
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->getUsers($args);
			break;

			case 2:
				$response = $this->getDetails($args);
			break;


			case 10:
				$response = $this->removeUser($args);
			break;

			case 11:
				$response = $this->createNewUser($args);
			break;

			case 100:
				$response = $this->pickupResource($args);
			break;

			case 101:
				$response = $this->dropResources($args);
			break;
			}

			if($args == null)
				echo $response;

			return $response;
		}

		private function getUsers($args)
		{
			$rql = "User(){r,l}";
			$c = $this->getConfig('rql');
			if($c != null)
				$rql .= $c;

			$res = $this->resManager->queryAssoc("$rql;");

			if(!$res) {
				if($args == null)
					return $this->jsonError("No users");
				else
					return 104;
			}
			$this->plib = LibLoad::obj('vpatch', 'profile', $this->db);
			$this->addProfileDetails($res, 'ref');
			if($args == null) {
				ob_start();
					json_encode_object(array('code'=>102,'data'=>$res), array('surname' => 'last'));
				return ob_get_clean();
			}

			return $res;
		}

		private function getDetails($args)
		{
			$vars = $this->argVar(array(
						"id" => "rid"), $args);

			$rql = "User({$vars->rid}){r,l}";
			$c = $this->getConfig('rql');

			if($c != null)
				$rql .= $c;

			$res = $this->resManager->queryAssoc("$rql;");

			if(!$res) {
				if($args == null)
					return $this->jsonError("No user");
				else
					return 104;
			}
			
			$this->plib = LibLoad::obj('vpatch', 'profile', $this->db);
			$this->addProfileDetails($res, 'ref');

			if($args == null) {
				ob_start();
					json_encode_object(array('code'=>102,'data'=>$res), array('surname' => 'last'));
				return ob_get_clean();
			}

			return $res;
		}

		private function addProfileDetails(&$list, $col)
		{
			$mpath = $this->plib->mediaPath();
			// this is horrible
			$ids = array();
			foreach($list as &$p) {
				if($p[$col] == 0) {
					$p['username'] = "Anon";
					$p['avatar'] = "$mpath/anon.png";
					continue;
				}

				if(isset($ids[$p[$col]])) {
					$p[] = $ids[$p[$col]][0];
					$p[] = $ids[$p[$col]][1];
					continue;
				}

				$this->plib->appendProfileDetails($p[$col], $p);

				$ids[$p[$col]] = array($p['ref'], "$mpath/{$p['avatar']}");
					
			}
		}

		private function createNewUser($args)
		{
			// check to see action is available
			$cfg = $this->getConfig("action");
			if($cfg == null) {
				if($args == null)
					return $this->jsonError("Unavailable");
				return 104;
			}

			$av = false;
			$cfg = explode(";", $cfg);
			foreach($cfg as $c)
				if($c == "new")
					$av = true;
			if(!$av) {
				if($args == null)
					return $this->jsonError("Unavailable");

				return 104;
			}
	
			include_once(LibLoad::shared('vpatch', 'users'));
			$ulib = new usersLibrary($this->db);
			$vars = $this->argvar(array('user' => 'user',
						'pass' => 'pass',
						'email' => 'email'), $_POST);

			if($ulib->getAccountFromUName($vars->user) != false) {
				if($args == null)
					return $this->jsonError("Account name already exists");
				return 104;
			}

			$hash = $ulib->generateHash($vars->pass);
			$salt = $ulib->getSalt();
			$id = null;
			if(!($id = $ulib->addAccount($vars->user, $hash, $salt, $vars->email))) {
				if($args == null)
					return $this->jsonError("Failed to create acocunt");
				return 104;
			}
			$rid = $this->resManager->addResource("User", $id, $vars->user);

			// create a new profile for the user
			// open-kura libraries
			$this->plib = LibLoad::obj('vpatch', 'profile', $this->db);
			$pid = $this->plib->addProfile($vars->user, "", "", "unknown.png");
			$ridp = $this->resManager->addResource("Profile", $pid, $vars->user);
			$this->resManager->createRelationship($rid, $ridp);

			$child = true;
			$r = null;

			if(($r  = $this->getConfig('parent')) != null)
				$child = true;
			else
			if(($r = $this->getConfig('child')) != null)
				$child = false;
			else {
				if($args == null)
					return $this->jsonSuccess("Create account");

				return 102;
			}
			$r = $this->resManager->queryAssoc("$r;");

			if($child)
				$this->resManager->createRelationship($r[0][0], $rid);
			else
				$this->resManager->createRelationship($rid, $r[0][0]);

			if($args == null) 
				return $this->jsonSuccess("Create account");
			return 102;
		}

		private function jsonError($message)
		{
			$str = "{\"code\":104,\n";
			$str .="\"data\": \"$message\"}";

			return $str;
		}

		private function jsonSuccess($message)
		{
			$str = "{\"code\":102,\n";
			$str .="\"data\": \"$message\"}";

			return $str;
		
		}

		/*
		*  JACK 100: Load resource into rbin
		*/
		public function pickupResource($args)
		{
			include_once(LibLoad::shared('vpatch', 'rbin'));
			$rlib = new rbinLibrary($this->db);
			if(!isset($_GET['krid']))
				return 104;
			$rql = "User({$_GET['krid']})";
			$c = $this->getConfig('rql');
			if($c != null)
				$rql .= $c;

			$res = $this->resManager->queryAssoc("$rql;");
			if(!$res)
				if($args == null)
					return $this->jsonError("Failed");

			$rlib->addResource($_GET['krid']);
			if($args == null)
				return $this->jsonSuccess("");

			return 102;
		}

		/*
		*  JACK 101: Pull resources from rbin and assoc
		*/
		public function dropResources($args)
		{
			include_once(LibLoad::shared('vpatch', 'rbin'));
			$vars = $this->argVar(array('kid' => 'id'), $args);

			$rlib = new rbinLibrary($this->db);

			$bin = $rlib->getBin();
			if($bin == null)
				return 104;

			$child = true;
			$r = null;
			if(($r = $this->getConfig('parent')) != null)
				$child = true;
			else
			if(($r = $this->getConfig('child')) != null)
				$child = false;
			else {
				if($args == null)
					return $this->jsonError("No relationship specified");
				return 104;
			}
			$rel = $this->resManager->queryAssoc("$r;");
			if(!$rel) {
				if($args == null)
					return $this->jsonError("No relationship specified");
				return 104;
			}
			foreach($bin as $k => $r) {
				if($r[2] == 0)
					continue;

				$stype = ResCast::cast($r[0]);

				if($stype['type'] != "User")
					continue;

				if($child) {
					echo "Adding as child";
					$this->resManager->createRelationship($rel[0][0], $k);
				}
				else {
					echo "Adding as parent";
					$this->resManager->createRelationship($k, $rel[0][0]);
				}

				$rlib->removeResource($k);
			}

			if($args == null)
				return $this->jsonSuccess("");

			return 102;
		}
		
		public function getConfigList()
		{
			return array("action", "parent", "child");
		}
	}
?>
