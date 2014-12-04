<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_rbmanagerComponent extends Component
	{
		private $rlib;
		public function initialize()
		{
			include(LibLoad::shared('vpatch', 'rbin'));
			$this->rlib = new rbinLibrary($this->db);
		}

		public function cerateInstance()
		{
			return 1;
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->getBin($args);
			break;

			case 2:
				$response = $this->removeRes($args);
			break;

			case 3:
				$response = $this->switchResource($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		private function getBin($args)
		{
			$bin = $this->rlib->getBin();
			if($args == null) {
				if(!$bin)
					return $this->jsonError("Nothing in bin");

				return $this->jsonBin($bin);
			}

			return $bin;
		}

		private function switchResource($args)
		{
			if(isset($_GET['rid']))
				$this->rlib->flagResource($_GET['rid']);

			$bin = $this->rlib->getBin();
			if($args == null) {
				if(!$bin)
					return $this->jsonError("Nothing in bin");

				return $this->jsonBin($bin);
			}

			return $bin;
		}

		private function removeRes($args)
		{
			if(isset($_GET['rid']))
				$this->rlib->removeResource($_GET['rid']);
			return 102;
		}

		private function jsonBin($list)
		{
			$sz = sizeof($list)-1;

			$str = "{\"code\":102,\n";
			$str .="\"data\": [\n";
			foreach($list as  $k => $l) {
				$str .= "\t{\n";
				$str .= "\t\t\"rid\":{$k},\n";
				$str .= "\t\t\"type\":{$l[0]},\n";
				$str .= "\t\t\"label\":\"{$l[1]}\",\n";
				$str .= "\t\t\"flag\":{$l[2]}\n";
				$str .= "\t}";

				if($sz-- > 0)
					$str .= ",";
				$str .= "\n";
			}

			$str .= "]}";
			return $str;
		}

		private function jsonError($msg)
		{
			$str = "{\"code\":104,\n";
			$str .="\"data\": \"$msg\"}\n";
			return $str;
		}

	}
?>
