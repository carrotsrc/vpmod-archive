<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_feed_uiPanel extends Panel
	{
		private $feed;
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_feed_ui');
		}

		public function loadTemplate()
		{
			/*
			*  this is a horrible hack:-
			*  because there is no surrounding
			*  node in the template, it won't 
			*  call this method. The moment there
			*  is a surround, it will mess up the
			*  feed because it automatically
			*  surrounds it with container divs
			*/
		}

		public function initialize($params = null)
		{
			$this->addComponentRequest(1, 101);

			parent::initialize();
		}
		public function applyRequest($result)
		{

			foreach($result as $rs) {
				switch($rs['jack']) {
				case 1:
					$this->feed = $rs['result'];
					echo $this->feed; // horrible hack (see above)
				break;
				}
			}
		}

		public function setAssets()
		{

		}
	}
?>
