<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

	/*
		this will handle dishing out the 
		ATOM Feeds
	*/

	class vp_feedComponent extends Component
	{
		private $resManager;
		public function initialize()
		{
			$this->resManager = Managers::ResourceManager();
		}

		public function createInstance($params = null)
		{
			$this->resManager = Managers::ResourceManager();
			$rids = $this->resManager->queryAssoc("Instance()<Component('vp_feed');");

			if($params != null) {
				$guid = $title = $subtitle = "";
				if(isset($params['guid']))
					$guid = $params['guid'];

				if(isset($params['title']))
					$title = $params['title'];

				if(isset($params['subtitle']))
					$subtitle = $params['subtitle'];

				include_once(LibLoad::shared('vpfeed', 'feed'));
				$flib = new feedLibrary($this->db);
				return $flib->setMetaData($guid, $title, $subtitle);
				
			}

			if(!$rids)
				return 1;

			return sizeof($rids);
		}

		public function run($channel = null, $args = null)
		{
			$response = null;

			switch($channel) {
			case 1:
				$response = $this->retrieveFeed($args);
			break;
			}

			if($args == null)
				echo $response;

			return $response;
		}

		public function retrieveFeed($args)
		{
			include_once(LibLoad::shared('vpfeed', 'feed'));
			$flib = new feedLibrary($this->db);

			// TODO: this is not good enough
			if(($k = $flib->getKey($this->instanceId)) != null) {
				if(!isset($_GET['fkey']) || $k != $_GET['fkey'])
					return $flib->emptyFeed();
			}

			$fobjs = $this->resManager->queryAssoc("Instance(){r}>(Instance('{$this->instanceId}')<Component('vp_feed'));");
			if(!$fobjs)
				return $flib->emptyFeed();

			foreach($fobjs as $k => $o) {
				// get cmpt of instance
				$c = $this->resManager->queryAssoc("Component(){l,r}>Instance({$o['id']});");
				if(!$c) {
					unset($fobjs[$k]);
					continue;
				}
				$fobjs[$k]['objl'] = $c[0]['label'];
				$fobjs[$k]['objr'] = $c[0]['ref'];
			}

			$feed = $flib->generateFeed($this->instanceId, $fobjs);
			return $feed;
		}
	}
?>
