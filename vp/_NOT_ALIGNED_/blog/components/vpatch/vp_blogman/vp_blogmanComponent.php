<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_blogmanComponent extends Component
	{
		private $resManager;
		private $blib;
		public function initialize()
		{
			$this->resManager = Managers::ResourceManager();
			include_once(LibLoad::shared('vpatch', 'vpblog'));
			$this->blib = new vpblogLibrary($this->db);
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->getPostListing($args);
			break;

			case 2:
				$response = $this->getPostDetails($args);
			break;

			case 3:
				$response = $this->addPost($args);
			break;

			case 4:
				$response = $this->updatePost($args);
			break;

			case 10:
				$response = $this->createBlog($args);
			break;

			case 11:
				$response = $this->getBlogs($args);
			break;

			case 20:
				$response = $this->addViewer($args);
			break;

			case 21:
				$response = $this->getViewers($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		private function getPostListing($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbl' => 'limit',
						'vpbp' => 'page'), $args);
			$chk = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_blogman'));");
			if(!$chk)
				return 104;

			return $this->blib->getPostList($vars->id, $vars->limit, $vars->page);
		}

		private function getPostDetails($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbi' => 'pid'
						), $args);
			$chk = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_blogman'));");
			if(!$chk)
				return 104;
			$post = $this->blib->getPost($vars->id, $vars->pid);

			return $post;
		}

		private function addPost($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbt' => 'title',
						'vpbc' => 'contents',
						'vpbs' => 'state'), $_POST);
			$ridb = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_blogman'));");
			if(!$ridb)
				return 104;
			$ridb = $ridb[0][0];

			$ridi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_blogman');");
			$ridi = $ridi[0][0];
			$pid = $this->blib->addPost($vars->id, $vars->title, $vars->contents, $vars->state);

			$ridp = $this->resManager->addResource("VPBlogPost", $pid, "VPB_$pid");

			$uid = Session::get('uid');
			$ridu = $this->resManager->queryAssoc("User('$uid');");
			if($ridu != false)
				$ridu = $ridu[0][0];

			$this->resManager->createRelationship($ridu, $ridp);
			$this->resManager->createRelationship($ridb, $ridp);
		}

		private function updatePost($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbi' => 'pid',
						'vpbt' => 'title',
						'vpbc' => 'contents',
						'vpbs' => 'state'), $_POST);
			$ridb = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_blogman'));");
			if(!$ridb)
				return 104;
			$this->blib->editPost($vars->id, $vars->pid, $vars->title, $vars->contents, $vars->state);

			return 102;
		}

		private function createBlog($args)
		{
			$vars = $this->argVar(array(
						'vpbt' => 'title',
						), $_POST);

			$id = $this->blib->createBlog($vars->title);
			if(!$id)
				return 104;

			$ridb = $this->resManager->addResource("VPBlog", $id, "VPBlog_$id");

			$ridi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_blogman');");
			$ridi = $ridi[0][0];
			$this->resManager->createRelationship($ridi, $ridb);
			$this->addTrackerParam('vpbbid', null);
		}

		private function getBlogs()
		{
			$rids = $this->resManager->queryAssoc("VPBlog()<(Instance('{$this->instanceId}')<Component('vp_blogman'));");
			$blogs = array();
			if(!$rids)
				return $blogs;

			foreach($rids as $rid) {
				$ref = $this->resManager->getHandlerRef($rid[0]);

				if(!$ref)
					continue;

				$details = $this->blib->getBlogDetails($ref);
				if(!$details)
					continue;

				$blogs[] = $details;
			}

			return $blogs;
		}

		private function addViewer($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbin' => 'title'
						), $_POST);

			$id = 1;
			$hi = 0;
			$rids = $this->resManager->queryAssoc("Instance()<Component('vp_blogview');");
			if($rids) {
				foreach($rids as $r) {
					$ref = $this->resManager->getHandlerRef($r[0]);
					if($ref > $hi)
						$hi = $ref;
				}
			}

			$id = $hi+1;
			$ridc = $this->resManager->queryAssoc("Component('vp_blogview');");
			$ridc = $ridc[0][0];
			$ridmi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_blogman');");
			$ridmi = $ridmi[0][0];
			$ridb = $this->resManager->queryAssoc("VPBlog('{$vars->id}');");
			$ridb = $ridb[0][0];

			$ridi = $this->resManager->addResource("Instance", $id, $vars->title);
			$this->resManager->createRelationship($ridc, $ridi);
			$this->resManager->createRelationship($ridi, $ridb);
			$this->resManager->createRelationship($ridmi, $ridi);
		}

		private function getViewers($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						), $args);
			$rids = $this->resManager->queryAssoc("(Instance()<Component('vp_blogview'))<(Instance('{$this->instanceId}')<Component('vp_blogman'));");
			$instances = array();
			if(!$rids)
				return $instances;

			foreach($rids as $r) {
				$res = $this->resManager->getResourceFromId($r[0]);
				$instances[] = $res;
			}

			return $instances;
		}
	}
?>
