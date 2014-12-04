<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class usercfg extends StrapBase
	{
		public function process(&$xml)
		{
			while(($tag = $xml->getNextTag()) != null) {
				if($tag->name == "/obj")
					break;

				if($tag->name == "user")
					$this->handleUser($tag);
			}
		}


		public function handleUser($tag)
		{
			$username = $tag->attributes['user'];
			$password = $tag->attributes['pass'];
			$email = $tag->attributes['email'];

			$out = null;
			$rout = null;
			global $log;

			if(isset($tag->attributes['out']))
				$out = $tag->attributes['out'];

			if(isset($tag->attributes['rout']))
				$rout = $tag->attributes['rout'];
			$ulib = LibLoad::obj('vpatch', 'users', $this->db);
			if($ulib->getAccountFromUName($username)) {
				$log[] = "! Username $username already exists";
				return;
			}

			$hash = $ulib->generateHash($password);
			$salt = $ulib->getSalt();

			$id = $ulib->addAccount($username, $hash, $salt, $email);
			$log[] = "+ Created User account $username with id $id";

			$rid = $this->resManager->addResource("User", $id, $username);
			$log[] = "+ Created User('$username') => $id";

			if($out != null)
				setVariable($out, $id);

			if($rout != null)
				setVariable($rout, $rid);
		}
	}
?>
