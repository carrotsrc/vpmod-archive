<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_blogview_feedLibrary extends FeedObj
	{
		public function feed($id, $link)
		{
			$posts = $this->getPosts($this->instanceId);
			if(!$posts)
				return "";

			$update = $posts[0][7];
			foreach($posts as $k => $p) {
				echo "<entry>\n";
				echo "\t<title>{$p[3]}</title>\n";
				echo "\t<link>$link</link>\n";
				echo "\t<id>{$id}{$p[0]}</id>\n";

				$dt = date("Y-m-d\TH:i:s\Z", $p[7]);
				echo "\t<updated>{$dt}</updated>\n";
				echo "\t<content>{$p[4]}</content>\n";
				echo "</entry>\n";
			}

			return intVal($update);
		}

		private function getPosts($instance)
		{
			$sql = "SELECT *, UNIX_TIMESTAMP(`posted`) FROM `vp_blogpost` WHERE `instance`='$instance' ORDER BY `post_id` DESC;";
			return $this->db->sendQuery($sql, false, false);
		}
	}
?>
