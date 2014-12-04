<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class usersLibrary extends DBAcc
	{
		private $salt;
		public function __construct($database)
		{
			$this->db = $database;
			$salt = null;
		}

		public function getSalt()
		{
			return $this->salt;
		}

		public function generateHash($password, $salt = null)
		{
			$cryptocheck = true;

			if($salt == null) {
				$salt =  md5(microtime());
				$salt .=  md5($salt);
				$this->salt = $salt;
			}

			$password = $this->obfuscatePassword($password, $salt);
			$tohash = $password.$salt;

			for($i = 0; $i < 7000; $i++) {
				$hasher = hash_init('sha256');
				hash_update($hasher, $tohash);
				$tohash = hash_final($hasher);
			}

			return $tohash;
		}

		/*
		*  So if the person has access to just the database
		*  it's not quite as easy as putting a dictionary with
		*  the salt.
		*/
		public function obfuscatePassword($password, $salt)
		{
			$saltx = bin2hex($salt);
			$sz = strlen($salt);
			$pwl = strlen($password);
			$ps = 0;
			$v = 0;
			for($i = 0; $i < $sz; $i++) {
				$s = hexdec($saltx[$i]);
				$v = bin2hex($password[$ps]) + $s;

				$v = hexdec($v);
				if($v > 126)
					$v = 33+($v-0x7E);
				else
				if($v < 33)
					$v = 126-(0x21 - $v);

				$password[$ps] = chr($v);

				$ps++;

				if($ps == $pwl)
					$ps = 0;
			}

			return $password;
		}

		public function addAccount($username, $hash, $salt, $email = null)
		{
			 if(!$this->arrayInsert('users', array('username' => $username,
									'hash' => $hash,
									'salt' => $salt,
									'email' => $email)))
				return null;

			return $this->db->getLastId();
		}

		public function getAccount($id)
		{
			$sql = "SELECT * FROM users WHERE id='$id'";
			return $this->db->sendQuery($sql, false, false);
		}

		public function updateAccount($id, $username, $hash, $salt)
		{
			return $this->arrayUpdate('users', array('username' => $username,
							'hash' => $hash,
							'salt' => $salt), "`id`='$id'");
		}

		public function removeAccount($id)
		{
			$sql = "DELECT FROM users WHERE id='$id'";
			return $this->db->sendQuery($sql, false, false);
		}

		public function getAccountFromUName($uname)
		{
			$sql = "SELECT * FROM users WHERE username='$uname'";
			return $this->db->sendQuery($sql);
		}

		public function setUserId($uid)
		{
			Session::set('uid', $uid);
		}

		public function logout()
		{
			Session::uset('uid');
		}
	}
?>
