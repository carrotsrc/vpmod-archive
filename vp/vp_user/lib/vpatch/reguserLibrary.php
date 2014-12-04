<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class reguserLibrary extends DBAcc
	{
		private $ulib = null;
		private $username = null;
		public function __construct($database)
		{
			$this->db = $database;
			$this->ulib = LibLoad::obj('vpatch', 'users', $this->db);
		}

		public function getUsername()
		{
			return $this->username;
		}

		public function addUser($username, $password, $email)
		{
			if($this->ulib->getAccountFromUName($username) != false)
				return false;

			if(!$this->checkUsername($username))
				return false;

			$key =  md5(microtime());
			$hash = $this->ulib->generateHash($password);
			$salt = $this->ulib->getSalt();


			
			$ins = $this->arrayInsert('registration_queue', array(
							'username' => $username,
							'hash' => $hash,
							'salt' => $salt,
							'email' => $email,
							'actkey' => $key
							));

			return true;
		}

		public function checkUsername($username)
		{
			if(!$this->db->sendQuery("SELECT id FROM registration_queue WHERE username='$username';", false, false))
				return true;
			else
				return false;
		}

		public function mailKey($username)
		{			
			$details = $this->db->sendQuery("SELECT email, actkey, username FROM registration_queue WHERE username='$username';", false, false);
			if(!$details)
				return false;

			$key = $details[0]['actkey'];
			$user = $details[0]['username'];
			$to      = $details[0]['email'];
			$subject = 'Account Activation';
			$link = "http://".SystemConfig::appServerRoot("index.php?loc=web/activate");
			$message = "
Hello $user,<br />
<br />
This email contains the link for activating your account with Kura.<br />
Please click the link (or if it's not clickable then copy the url beneath into the address bar).<br />
<br />
Link:<br />
<a href=\"$link&urkey=$key\">Activate</a><br />
<br />
URL:<br />
$link&urkey=$key<br />
<br />
thank you for your co-operation,<br />
The benevolent admin<br />
---<br />
admin@carrotsrc.org<br />
";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: noreply@carrotsrc.org' . "\r\n" .
			    'Reply-To: noreply@carrotsrc.org' . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$result = mail($to, $subject, $message, $headers);
		}

		public function activateUser($key)
		{
			$details = $this->db->sendQuery("SELECT * FROM registration_queue WHERE actkey='$key';", false, false);
			if(!$details)
				return false;

			$user = $details[0]['username'];
			$hash = $details[0]['hash'];
			$salt = $details[0]['salt'];
			$email = $details[0]['email'];
			$chk = $this->ulib->addAccount($user, $hash, $salt, $email);
			if(!$chk)
				return false;
			$this->username = $user;
			return $this->db->getLastId();
		}

		public function removeKeyFromQueue($key)
		{
			return $this->db->sendQuery("DELETE FROM registration_queue WHERE actkey='$key';");
		}

		public function getRegistrationQueue()
		{
			return $this->db->sendQuery("SELECT * FROM registration_queue;", false, false);
		}

	}
?>
