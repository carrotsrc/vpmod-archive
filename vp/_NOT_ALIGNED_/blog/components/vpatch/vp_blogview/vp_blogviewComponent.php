<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_blogviewComponent extends Component
	{
		private $resManager;
		private $blogId;
		private $blib;
		public function initialize()
		{
			$this->resManager = Managers::ResourceManager();
			$rid = $this->resManager->queryAssoc("VPBlog()<(Instance('{$this->instanceId}')<Component('vp_blogview'));");
			if(!$rid) {
				$this->blogId = null;
				return;
			}

			include_once(LibLoad::shared('vpatch', 'vpblog'));
			$this->blib = new vpblogLibrary($this->db);
			$this->blogId = $this->resManager->getHandlerRef($rid[0][0]);
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->getPosts($args);
			break;

			case 2:
				$response = $this->getBlogDetails($args);
			break;

			case 3:
				$response = $this->getPost($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		private function getPosts($args)
		{
			if($this->blogId == null)
				return 104;

			$posts = $this->blib->getPosts($this->blogId, 10, 1);
			return $posts;
		}

		private function getBlogDetails($args)
		{
			if($this->blogId == null)
				return 104;

			$details = $this->blib->getBlogDetails($this->blogId);
			return $details;
		}

		private function getPost($args)
		{
			$vars = $this->argVar(array(
						'vbpost' => 'id',
						), $args);
			$post = $this->blib->getPost($this->blogId, $vars->id);
			return $post[0];
		}
	}
?>
