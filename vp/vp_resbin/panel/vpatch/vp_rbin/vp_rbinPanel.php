<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	class vp_rbinPanel extends Panel
	{
		private $bin;
		public function __construct()
		{
			parent::__construct();
			$this->setModuleName('vp_rbin');
			$this->bin = null;
			$this->jsCommon = "VP_RbinInterface";
		}

		public function loadTemplate()
		{
			$spath = SystemConfig::appRelativePath(Managers::AppConfig()->setting('submitrequest'));
			$aid = Session::get('aid');
			$area = Managers::ResourceManager()->getHandlerRef($aid);
			$qstr = "$spath?cpl=$area/{$this->componentId}/{$this->instanceId}/2";
			$qstr .= $this->globalParamStr();
			echo "<div class=\"rbin-container\">";
			echo "<b>Resource Bin</b><div id=\"{$this->moduleName}{$this->id}-list\">";
				if($this->bin == 104 || $this->bin == null) {
					echo "Empty";
				}
				else {
					foreach($this->bin as $k => $r) {
						echo "<div class=\"rbin-resource\"> ";
						echo $r[1];
						echo " <a href=\"$qstr&rid={$k}\" style=\"color: grey\">X</a> ";
						echo "</div>";
					}
				}
				echo "</div>";
			echo "</div>";
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
					$this->bin = $rs['result'];
				break;
				}

			}
		}

		public function setAssets()
		{
			$this->addAsset('js', "/G/toolset.js");
			$this->addAsset('js', "/G/resbin_sc.js");
			$this->addAsset('js', "rbin_sc.js");
		}
	}
?>
