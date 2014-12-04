<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_sblogview_feedLibrary extends FeedObj
	{
		private $blogId;
		public function feed($id, $link)
		{
			$posts = $this->getPosts($this->instanceId);
			$pstr = $this->getParser();
			$parser = null;
			if($pstr)
				$parser = LibLoad::obj('txparse', "{$pstr}_Parser");

			if(!$posts)
				return "";
			$update = $posts[0]['timestamp'];
			foreach($posts as $k => $p) {
				echo "<entry>\n";
				echo "\t<title>{$p['title']}</title>\n";
				echo "\t<link>$link</link>\n";
				echo "\t<id>{$id}{$p['id']}</id>\n";

				$dt = date("Y-m-d\TH:i:s\Z", $p['timestamp']);
				echo "\t<updated>{$dt}</updated>\n";
				if($parser) {
					$p['contents'] = $parser->parse($p['contents'], "", null);
					echo "\t<content type=\"xhtml\">";
				} else
					echo "\t<content type=\"text\">";

					echo "{$p['contents']}</content>\n";

				echo "</entry>\n";
			}

			return intVal($update);
		}

		private function getPosts($instance)
		{
			$rman = Managers::ResourceManager();
			$instance = $rman->queryAssoc("VPBlog(){r}<(Instance('$instance')<Component('vp_sblogview'));");
			if(!$instance)
				return false;
			$this->blogId = $instance[0]['ref'];

			$sql = "SELECT *, UNIX_TIMESTAMP(`posted`) AS `timestamp` FROM `vp_blogpost` WHERE `instance`='{$this->blogId}' ORDER BY `post_id` DESC;";
			return $this->db->sendQuery($sql);
		}

		private function getParser()
		{
			$p = $this->db->sendQuery("SELECT `parser` FROM `vp_blog` WHERE `id`='{$this->blogId}'");
			if(!$p || $p[0]['parser'] == "")
				return null;

			return $p[0]['parser'];
		}
	}
?>
