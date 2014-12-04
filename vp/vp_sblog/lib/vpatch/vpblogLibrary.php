<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vpblogLibrary extends DBAcc
	{
		public function __construct($database)
		{
			$this->db = $database;
		}

		public function getNextPostId($inst)
		{
			$res = $this->db->sendQuery("SELECT post_id FROM vp_blogpost WHERE instance='$inst' ORDER BY post_id DESC LIMIT 1");
			if(!$res)
				return 1;
			$res = intval($res[0]['post_id']);
			$res++;
			return $res;
		}

		public function addPost($inst, $title, $contents, $state = 0)
		{
			$postId = $this->getNextPostId($inst);

			if(!$this->arrayInsert('vp_blogpost', array(
							'instance' => $inst,
							'title' => $title,
							'contents' => $contents,
							'state' => $state,
							'post_id' => $postId,
							)))
				return false;

			return $this->db->getLastId();
		}

		public function editPost($inst, $id, $title = null, $contents = null, $state = 0)
		{
			$update = array();
			if($title != null)
				$update['title'] = $title;

			if($contents != null)
				$update['contents'] = $contents;

			if($state != null)
				$update['state'] = $state;


			return $this->arrayUpdate('vp_blogpost', $update, "instance='$inst' AND post_id='$id'");
		}

		public function deletePost($inst, $id)
		{
			return $this->db->sendQuery("DELETE FROM vp_blogpost WHERE instance='$inst' AND post_id='$id';", false, false);
		}

		public function getPost($inst, $id)
		{
			return $this->db->sendQuery("SELECT * FROM vp_blogpost WHERE instance='$inst' AND post_id='$id';", false, false);
		}

		public function getPosts($inst, $limit = null, $page = NULL)
		{
			$sql = "SELECT * FROM vp_blogpost WHERE instance='$inst' ORDER BY posted DESC";
			if($limit != null) {
				if($page == null)
					$page = 1;

				$targ = ($limit * ($page-1));
				$sql .= " LIMIT $limit OFFSET $targ";
			}

			return $this->db->sendQuery($sql);
		}

		public function getPostList($inst, $limit = null, $page = NULL)
		{
			$sql = "SELECT id, title, posted, post_id FROM vp_blogpost WHERE instance='$inst' ORDER BY posted DESC";
			if($limit != null) {
				if($page == null)
					$page = 1;

				$targ = ($limit * ($page-1));
				$sql .= " LIMIT $limit OFFSET $targ";
			}

			return $this->db->sendQuery($sql);
		}

		public function createBlog($title, $parser)
		{
			if(!$this->arrayInsert('vp_blog', array('title' => $title, 'parser' => $parser)))
				return false;

			return $this->db->getLastId();
		}

		public function getBlogDetails($id)
		{
			return $this->db->sendQuery("SELECT id, title, parser FROM vp_blog WHERE id='$id';");
		}
	}
?>
