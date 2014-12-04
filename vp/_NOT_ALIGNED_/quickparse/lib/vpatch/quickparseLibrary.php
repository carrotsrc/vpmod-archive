<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	include_once(SystemConfig::appRootPath("system/helpers/vpxml.php"));
	class quickparseLibrary
	{
		private $vxml;
		public function __construct()
		{
			
		}

		public function init($xml)
		{
			$this->vxml = new VPXML();
			$this->vxml->init($xml);
		}

		public function findElement($element, $count = 1)
		{
			while(($tag = $this->vxml->getNextTag()) != null)
				if($tag->name == $element)
					if($count-- == 1)
						return $tag;

			return null;
		}

		public function getNextTag()
		{
			return $this->vxml->getNextTag();
		}

	}
?>
