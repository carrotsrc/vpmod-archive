<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 *
 * vegPatch Sblog is a richer version of the original blog with the
 * addition of attachment handling. Pulled from openKura's blog.
 *
 * It piggy backs off of the vp_blog.
 */
	class vp_sblogmanComponent extends Component
	{
		private $resManager;
		private $blib;
		public function initialize()
		{
			$this->resManager = Managers::ResourceManager();
			$this->blib = LibLoad::obj('vpatch', 'vpblog', $this->db);
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

			case 12:
				$response = $this->getParsers($args);
			break;

			case 20:
				$response = $this->addViewer($args);
			break;

			case 21:
				$response = $this->getViewers($args);
			break;

			case 30:
				$response = $this->uploadAttachment($args);
			break;

			case 31:
				$response = $this->getBlogAttachments($args);
			break;
			}

			if($args == null)
				echo $response;

			 return $response;
		}

		private function getParsers($args)
		{
			$path = SystemConfig::relativeAppPath("library/lib/txparse");
			if(!is_dir($path))
				return 104;

			$fm = new FileManager();
			$files = $fm->listFiles($path);
			if(!$files)
				return 104;

			foreach($files as &$f) {
				$f = explode("_", $f);
				$f = $f[0];
			}

			return $files;
		}

		private function getPostListing($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbl' => 'limit',
						'vpbp' => 'page'), $args);
			$chk = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman'));");
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
			$chk = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman'));");
			if(!$chk)
				return 104;
			$post = $this->blib->getPost($vars->id, $vars->pid);

			$alib = LibLoad::obj('vpatch', 'attachment',$this->db);
			
			$catt = $this->resManager->queryAssoc("[attachment]{r}<(VPBlogPost('{$vars->pid}')<(VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman'))));");
			$cls = array();
			if($catt) {
				// filter out the currently attached resources
				foreach($catt as $rid)
					$cls[] = $rid['ref'];
				$cls = $alib->getlsAttachment($cls);
			}

			$post[] = $cls;

			return $post;
		}

		private function addPost($args)
		{
			// the form is combined so if there are files
			// then handle files instead of adding a post
			if(isset($_FILES['vpbu']) && $_FILES['vpbu']['size'] > 0) {
				return $this->uploadAttachment($args);
			}
			$attachments = array();

			foreach($_POST as $p => $v) {
				if(strlen($p) > 4 && substr($p, 0, 4) == "ainc")
					$attachments[] = $v;
			}
			if(sizeof($attachments) > 0) {
				// get the attachment rids if there are any attached
				$alib = LibLoad::obj('vpatch', 'attachment',$this->db);

				if(($ls = $alib->getlsAttachment($attachments))) {
					$attachments = array();
					foreach($ls as $l) {
						$r = $this->resManager->queryAssoc("{$l['name']}('{$l['id']}');");

						if($r)
							$attachments[] = $r[0]['id'];
					}
				}
			}

			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbt' => 'title',
						'vpbc' => 'contents',
						'vpbs' => 'state'), $_POST);
			$ridb = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman'));");
			if(!$ridb)
				return 104;
			$ridb = $ridb[0]['id'];

			$ridi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_sblogman');");
			$ridi = $ridi[0]['id'];
			$pid = $this->blib->addPost($vars->id,  StrSan::mysqlSanatize($vars->title), StrSan::mysqlSanatize($vars->contents), $vars->state);

			$ridp = $this->resManager->addResource("VPBlogPost", $pid, "VPB_$pid");

			$uid = Session::get('uid');
			$ridu = $this->resManager->queryAssoc("User('$uid');");
			if($ridu != false)
				$ridu = $ridu[0]['id'];

			$this->resManager->createRelationship($ridu, $ridp);
			$this->resManager->createRelationship($ridb, $ridp);

			foreach($attachments as $a)
				$this->resManager->createRelationship($ridp, $a);
		}

		private function updatePost($args)
		{
			if(isset($_FILES['vpbu']) && $_FILES['vpbu']['size'] > 0)
				return $this->uploadAttachment($args);

			$vars = $this->argVar(array(
						'vpbb' => 'id',
						'vpbi' => 'pid',
						'vpbt' => 'title',
						'vpbc' => 'contents',
						'vpbs' => 'state'), $_POST);
			
			$ridp = null;

			// attachments for removal and inclusion
			$arem = array();
			$ainc = array();

			foreach($_POST as $p => $v) {
				if(strlen($p) > 4 && substr($p, 0, 4) == "arem")
					$arem[] = $v;
				else
				if(strlen($p) > 4 && substr($p, 0, 4) == "ainc")
					$ainc[] = $v;
			}

			// remove any attachment queued for removal
			if(sizeof($arem) > 0) {
				$ridp = $this->resManager->queryAssoc("VPBlogPost('$vars->pid')<(VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman')));");
				$alib = LibLoad::obj('vpatch', 'attachment',$this->db);
				$arem = $alib->getlsAttachment($arem);

				foreach($arem as $l) {
					$rida = $this->resManager->queryAssoc("{$l['name']}('{$l['id']}');");
					if($rida)
						$this->resManager->removeRelationship($ridp[0]['id'], $rida[0]['id']);
				}
			}

			// include any attachments queued
			if(sizeof($ainc) > 0) {
				if($ridp == null)
					$ridp = $this->resManager->queryAssoc("VPBlogPost('$vars->pid')<(VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman')));");

				$alib = LibLoad::obj('vpatch', 'attachment',$this->db);
				$ainc = $alib->getlsAttachment($ainc);

				foreach($ainc as $l) {
					$rida = $this->resManager->queryAssoc("{$l['name']}('{$l['id']}');");
					if($rida)
						$this->resManager->createRelationship($ridp[0]['id'], $rida[0]['id']);
				}
			}

			$ridb = $this->resManager->queryAssoc("VPBlog('{$vars->id}')<(Instance('$this->instanceId')<Component('vp_sblogman'));");
			if(!$ridb)
				return 104;
			$this->blib->editPost($vars->id, $vars->pid, $vars->title, $vars->contents, $vars->state);

			return 102;
		}

		private function createBlog($args)
		{
			$vars = $this->argVar(array(
						'vpbt' => 'title',
						'vpbp' => 'parser'
						), $_POST);

			if($vars->parser == 'null')
				$vars->parser = "";

			$id = $this->blib->createBlog($vars->title, $vars->parser);
			if(!$id)
				return 104;

			$ridb = $this->resManager->addResource("VPBlog", $id, "VPBlog_$id");

			$ridi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_sblogman');");
			$ridi = $ridi[0]['id'];
			$this->resManager->createRelationship($ridi, $ridb);
			$this->addTrackerParam('vpbbid', null);
		}

		private function getBlogs()
		{
			$rids = $this->resManager->queryAssoc("VPBlog(){r}<(Instance('{$this->instanceId}')<Component('vp_sblogman'));");
			$blogs = array();
			if(!$rids)
				return $blogs;

			foreach($rids as $rid) {
				$ref = $rid['ref'];

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
			$rids = $this->resManager->queryAssoc("Instance()<Component('vp_sblogview');");
			if($rids) {
				foreach($rids as $r) {
					$ref = $this->resManager->getHandlerRef($r['id']);
					if($ref > $hi)
						$hi = $ref;
				}
			}

			$id = $hi+1;
			$ridc = $this->resManager->queryAssoc("Component('vp_sblogview');");
			$ridc = $ridc[0]['id'];
			$ridmi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_sblogman');");
			$ridmi = $ridmi[0]['id'];
			$ridb = $this->resManager->queryAssoc("VPBlog('{$vars->id}');");
			$ridb = $ridb[0]['id'];

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
			$rids = $this->resManager->queryAssoc("(Instance()<Component('vp_sblogview'))<(Instance('{$this->instanceId}')<Component('vp_sblogman'));");
			$instances = array();
			if(!$rids)
				return $instances;

			foreach($rids as $r) {
				$res = $this->resManager->getResourceFromId($r['id']);
				$instances[] = $res;
			}

			return $instances;
		}

		private function uploadAttachment($args)
		{
			$alib = LibLoad::obj('vpatch', 'attachment',$this->db);
			$path = $alib->handleUpload($_FILES['vpbu']);
			$bid = null;

			if(isset($_POST['vpbb']))
				$bid = $_POST['vpbb'];

			if($path == null)
				return 104;

			$t = $alib->getType($path);
			$f = explode("/", $path);
			$f = $f[sizeof($f)-1];
			$path = explode("library", $path);
			$path = "library/media/attm/" . $path[sizeof($path)-1];
			$ref = $alib->addAttachment($t['id'], $_FILES['vpbu']['name'], $path);
			$rid = $this->resManager->addResource($t['name'], $ref, $f);

			$ridi = $this->resManager->queryAssoc("Instance('{$this->instanceId}')<Component('vp_sblogman');");
			$ridi = $ridi[0]['id'];
			$this->resManager->createRelationship($ridi, $rid);

			if($bid == null)
				return 102;

			$ridb = $this->resManager->queryAssoc("VPBlog('{$bid}')<(Instance('{$this->instanceId}')<Component('vp_sblogman'));");
			if(!$ridb)
				return 104;
			$ridb = $ridb[0]['id'];
			$this->resManager->createRelationship($ridb, $rid);
			return 102;
		}

		private function getBlogAttachments($args)
		{
			$vars = $this->argVar(array(
						'vpbb' => 'id'
						), $args);

			$rids = $this->resManager->queryAssoc("[attachment]{r}<VPBlog('{$vars->id}');");
			if(!$rids)
				return 104;

			$alib = LibLoad::obj('vpatch', 'attachment',$this->db);
			$ls = array();
			foreach($rids as $rid)
				$ls[] = $rid['ref'];
			$ls = $alib->getlsAttachment($ls);
			if($args == null)
				return;

			return $ls;
		}
	}
?>
