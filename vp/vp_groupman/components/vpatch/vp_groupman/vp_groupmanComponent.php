<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_groupmanComponent extends Component
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
			if(($r = $this->resManager->queryAssoc("Instance()<Component('vp_groupman');"))) {
				$id = sizeof($r[0])+1;
			}

			return $id;
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->getGroups($args);
			break;

			case 2:
				$response = $this->getUsers($args);
			break;

			case 3:
				$response = $this->addGroup($args);
			break;

			case 4:
				$response = $this->getEdges($args);
			break;

			case 10:
				$response = $this->removeUser($args);
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

		private function getGroups($args)
		{
			$rql = "Group(){l}";

			$c = $this->getConfig('rql');
			if($c != null)
				$rql .= $c;
			$res = $this->resManager->queryAssoc("$rql;");
			if(!$res) {
				if($args == null)
					return $this->jsonError("No groups");
				else
					return 104;
			}
			if($args == null) {
				ob_start();
				json_encode_object(array('code'=>'102', 'data' => $res));
				return ob_get_clean();
			}

			return $res;
		}

		private function getUsers($args)
		{
			$vars = $this->argVar(array(
						"gid" => "id"), $args);

			$prefix = "User(){r,l}";
			$rql = "Group({$vars->id})";

			$c = $this->getConfig('rql');

			if($c != null) {
				$rql .= $c;
				$rql = "$prefix<($rql)";

			}
			else
				$rql = "$prefix<$rql;";

			$res = $this->resManager->queryAssoc($rql);

			if(!$res) {
				if($args == null)
					return $this->jsonError("No users");
				else
					return 104;
			}
			if($args == null) {
				ob_start();
				json_encode_object(array('code'=>'102', 'data' => $res));
				return ob_get_clean();
			}

			return $res;
		}

		private function getEdges($args)
		{
			$c = $this->getConfig("edges");
			if($c == null)
				return 104;
			$edges = explode(";",$c);
			$list = array();
			foreach($edges as $e) {
				if($e == "*") {
					$list = $this->resManager->getEdgesOfType('Group');
					if($args == null)
						return $this->jsonEdges($list);

					return $list;
				}

				$id = $this->resManager->getEdge($e);
				if(!$id)
					continue;

				$list[] = array($id, $e);
			}

			if($args == null)
				return $this->jsonEdges($list);

			return $list;
		}

		private function checkGroup($gid)
		{

			$c = $this->getConfig('rql');
			if($c == null)
				return true;

			$rql = "Group($gid)";
			$rql .= $c;
			$res = $this->resManager->queryAssoc("$rql;");
			if(!$res)
				return false;

			return true;
		}

		private function removeUser($args)
		{
			$vars = $this->argVar(array(
						"gid" => "id",
						"rid" => "rid"), $args);

			if(!$this->checkGroup($vars->gid)) {
				if($args == null)
					return $this->jsonError("Invalid group specified");

				return 104;
			}

			if(!$this->resManager->removeRelationship($vars->id, $vars->rid)) {
				if($args == null)
					return $this->jsonError("Error occured");
				return 104;
			}
			if($args == null)
				return $this->jsonSuccess("");

			return 102;
		}

		private function addGroup($args)
		{
			$vars = $this->argVar(array(
						"gname" => "name"), $_POST);
			$vars->name = str_replace(" ", "_", $vars->name);
			$rid = $this->resManager->addResource("Group", 0, $vars->name);

			if(($c = $this->getConfig('parent')) != null) {
				$res = $this->resManager->queryAssoc("$c;");
				if(!$res)
					return $this->jsonError("Invalid parent");

				$this->resManager->createRelationship($res[0]['id'], $rid);
			}

			return $this->jsonSuccess("Created group");
		}


		private function jsonEdges($list)
		{
			if(!$list)
				$list = array();
			$sz = sizeof($list)-1;

			$str = "{\"code\":102,\n";
			$str .="\"data\": [\n";
			foreach($list as $l) {
				$str .= "\t{\n";
				$str .= "\t\t\"id\":{$l['id']},\n";
				$str .= "\t\t\"edge\":\"{$l['label']}\"\n";
				$str .= "\t}";

				if($sz-- > 0)
					$str .= ",";
				$str .= "\n";
			}

			$str .= "]}";
			return $str;
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
			$id = null;
			if(!isset($_GET['grid'])) {
				if(!isset($_GET['urid']))
					return 104;
				else
					$id = $_GET['urid'];
			}
			else
				$id = $_GET['grid'];


			$rlib->addResource($id);
			return 102;
		}

		/*
		*  JACK 101: Pull resources from rbin and assoc
		*/
		public function dropResources($args)
		{
			include_once(LibLoad::shared('vpatch', 'rbin'));
			$vars = $this->argVar(array('gid' => 'group', 'edge' => 'edge'), $args);

			$rlib = new rbinLibrary($this->db);

			$bin = $rlib->getBin();
			if($bin == null)
				return 104;

			$res = $this->resManager->getResourceFromId($vars->rid);
			foreach($bin as $k => $r) {
				if($r[2] == 0)
					continue;
				$this->resManager->createRelationship($vars->group, $k, $vars->edge);
				$rlib->removeResource($k);
			}

			return 102;
		}

		public function getConfigList()
		{
			return array('edges', 'parent', 'rql');
		}
	}
?>
