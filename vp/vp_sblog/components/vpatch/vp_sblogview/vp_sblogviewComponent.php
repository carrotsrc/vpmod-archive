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
 * vegPatch splogview is the viewer for vegPatch sblogs
 */
	class vp_sblogviewComponent extends Component
	{
		private $resManager;
		private $blogId;
		private $blib;
		public function initialize()
		{
			$this->resManager = Managers::ResourceManager();
			$rid = $this->resManager->queryAssoc("VPBlog(){r}<(Instance('{$this->instanceId}')<Component('vp_sblogview'));");
			if(!$rid) {
				$this->blogId = null;
				return;
			}

			$this->blib = LibLoad::obj('vpatch', 'vpblog', $this->db);
			$this->blogId = $rid[0]['ref'];
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
			if(!$posts)
				return null;
				
			$alib = LibLoad::obj('vpatch', 'attachment', $this->db);

			$parser = $this->getParser();
			if($parser != null) {
				include_once(LibLoad::shared('txparse', "{$parser}_Parser"));
				$pclass = "{$parser}_ParserLibrary";
				$parser =  new $pclass();
			}


			foreach($posts as &$p) {
				$cls = array();
				$att = $this->resManager->queryAssoc("[attachment]{r}<(VPBlogPost('{$p['post_id']}')<VPBlog('{$this->blogId}'));");
				
				// this is horrible and will query the attachment every time
				// even if it is duplicated in another post
				// TODO: fix this when time is less pressing
				if($att) {
				foreach($att as $a) 
					$cls[] = $a['ref'];
				}
	

				if(sizeof($cls) > 0) {
					$cls = $alib->getlsAttachment($cls);
				}
				else
					$cls = null;
				
				$p['attachments'] = $cls;


				if($parser != null)
					$p['contents'] = $parser->parse($p['contents'], "?loc=", $p['attachments']);
			}

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
			$alib = LibLoad::obj('vpatch', 'attachment', $this->db);
			$post = $this->blib->getPost($this->blogId, $vars->id);
			if(!$post)
				return null;
				
			$post = $post[0];

			$parser = $this->getParser();

			if($parser == null)
				return $posts;


			include_once(LibLoad::shared('txparse', "{$parser}_Parser"));
			$pclass = "{$parser}_ParserLibrary";
			$parser =  new $pclass();


			$cls = array();
			$att = $this->resManager->queryAssoc("[attachment]{r}<(VPBlogPost('{$post['post_id']}')<VPBlog('{$this->blogId}'));");
			
			// this is horrible and will query the attachment every time
			// even if it is duplicated in another post
			// TODO: fix this when time is less pressing
			if($att) {
				foreach($att as $a) 
					$cls[] = $a['ref'];
			}


			if(sizeof($cls) > 0)
				$cls = $alib->getlsAttachment($cls);
			else
				$cls = null;

			$post['attachments'] = $cls;

			if($parser)
				$post['contents'] = $parser->parse($post['contents'], "?loc=", $post['attachments']);

			return $post;
		}

		private function getParser()
		{
			$d = $this->getBlogDetails(null);
			if($d == null)
				return null;
			if($d[0]['parser'] == "")
				return null;

			return $d[0]['parser'];
		}
	}
?>
