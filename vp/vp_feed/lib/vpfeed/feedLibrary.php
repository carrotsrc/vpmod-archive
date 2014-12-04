<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
 	include("feedobj.php");
	class feedLibrary extends DBAcc
	{
		public function __construct($database)
		{
			$this->db = $database;
		}

		public function generateFeed($instance, array $obj)
		{
			// get the feed meta data
			$meta = $this->getMetaData($instance);
			$mt = $latest = intVal($meta[0]['timestamp']);


			// get the feed from the objects
			ob_start();
			foreach($obj as $o) {
				$c = $o['objl'];
				include_once(LibLoad::shared('vpfeed/fobj', "{$c}_feed"));
				$cname = "{$c}_feedLibrary";
				$olib = new $cname($this->db, $o['objr'], $o['ref']);
				$updated = $olib->feed($meta[0]['guid'], "link?loc");

				// check to see if this is the latests update
				if($updated > $latest)
					$latest = $updated;
			}


			$feed = ob_get_contents();
			ob_end_clean();


			// generate the header
			// RFC standard
			$dt = date("Y-m-d\TH:i:s\Z", $latest);

			ob_start();
			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			echo "<feed xmlns=\"http://www.w3.org/2005/Atom\">\n";

			echo "<id>{$meta[0]['guid']}</id>\n";
			echo "<title>{$meta[0]['title']}</title>\n";
			echo "<subtitle>{$meta[0]['subtitle']}</subtitle>\n";
			echo "<updated>{$dt}</updated>\n";
			echo $feed;
			echo "</feed>";
			$feed = ob_get_contents();
			ob_end_clean();


			// update if it is the latest time
			if($latest > $mt)
				$this->setupdate($instance, $latest);

			return $feed;
		}


		public function emptyFeed()
		{
			$feed = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$feed .= "<feed xmlns=\"http://www.w3.org/2005/Atom\"><title>Feed Error</title><id>84205c4d-a6d3-4f61-b352-dece686df5a9</id></feed>\n";
			return $feed;
		}

		public function setMetaData($guid, $title, $subtitle)
		{
			if(!$this->arrayInsert('vp_feed', array(
							'guid' => $guid,
							'title' => $title,
							'subtitle' => $subtitle,
							'updated' => 'NOW()'
							))) {
				return false;
			}
			$id = $this->db->getLastId();
			$this->setUpdate($id, time('now'));
			return $id;
		}

		public function setUpdate($id, $timestamp)
		{
			$dt = date("Y-m-d H:i:s", $timestamp);
			$this->arrayUpdate('vp_feed', array(
							'updated' => $dt
							), "`id`='$id'");
		}

		private function getMetaData($id)
		{
			$sql = "SELECT `id`, `guid`, `title`, `subtitle`, `updated`, UNIX_TIMESTAMP(`updated`) AS `timestamp` FROM `vp_feed` WHERE `id`='$id';";
			return $this->db->sendQuery($sql, false, false);
		}

		public function getKey($id)
		{
			$sql = "SELECT `key` FROM `vp_feed` WHERE `id`='$id';";
			if(($c = $this->db->sendQuery($sql, false, false)))
				return $c[0][0];

			return null;
		}
	}
?>
