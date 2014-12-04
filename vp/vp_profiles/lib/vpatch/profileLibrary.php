<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class profileLibrary extends DBAcc
	{
		public function __construct($database)
		{
			$this->db = $database;
		}

		public function getProfiles($rid)
		{
			
		}

		public function getAvatar($profile = null)
		{

		}

		public function getProfile($id)
		{
			$res = $this->db->sendQuery("SELECT * FROM profiles WHERE id='$id';", false, false);
			if(!$res)
				return null;

			return $res;
		}
		
		public function getProfileOf($uid)
		{
			$res = $this->db->sendQuery("SELECT `first`,`surname`,`status`,`avatar` FROM `profiles` WHERE `owner`='$uid';", false, false);
			if(!$res)
				return null;

			return $res;
		}

		public function addProfile($fname, $sname, $status, $avatar)
		{
			if(!$this->arrayInsert('profiles', array('first' => $fname,
								'surname' => $sname, 
								'status' => $status,
								'avatar' => $avatar)))
				return null;

			return $this->db->getLastId();
		}

		public function mediaPath()
		{
			return SystemConfig::appRelativePath('library/media/profile');
			
		}

		public function profileArea($user)
		{
			return "?loc=profiles&profile=$user";
		}

		private function getNextSeq($profile)
		{
			$id = $this->db->sendQuery("SELECT seq FROM profile_item WHERE profile='$profile' ORDER BY seq DESC LIMIT 1");
			if(!$id)
				return 1;

			return (intVal($id[0]['seq'])+1);
		}

		private function profileItemExists($profile, $type)
		{
			$r = $this->db->sendQuery("SELECT id FROM profile_item WHERE type='$type' WHERE profile='$profile';");
			if(!$r)
				return false;

			return true;
		}

		public function updateProfile($profile, $first, $last)
		{
			return $this->arrayUpdate('profiles', array (
								'first' => $first,
								'surname' => $last),
								"id='$profile'");
		}

		public function addProfileItem($profile, $type)
		{
			if($this->profileItemExists($profile, $type))
				return null;

			$seq = $this->getNextSeq($profile);

			$id = $this->arrayInsert('profile_item', array(
									'profile' => $profile,
									'type' => $type,
									'seq' => $seq,
									'value' => ""
									));

			if(!$id)
				return null;

			return $this->db->getLastId();
		}

		public function updateProfileItem($profile, $type, $value)
		{
			return $this->arrayUpdate('profile_item', array(
									'value' => $value
									), "profile='$profile' AND type='$type'");
		}

		public function removeProfileItem($profile, $type)
		{
			return $this->db->sendQuery("DELETE FROM profile_item WHERE type='$type' AND profile='$profile';");
		}

		public function updateHeader($profile, $header)
		{
			return $this->arrayUpdate('profiles', array(
							'header' => $header
							), "id='$profile';");
		}

		public function appendProfileDetails($uid, array &$destination)
		{
			$d = $this->getProfileOf($uid);
			if(!$d) // put in boilerplate details
				$d = array('first' => 'Anon', 'surname' => 'User', 'status' => '', 'avatar' => 'anon.png');
			else
				$d = $d[0];

			$d['avatar'] = $this->mediaPath()."/{$d['avatar']}";
			$destination = array_merge($destination, $d);

		}
	}
?>
