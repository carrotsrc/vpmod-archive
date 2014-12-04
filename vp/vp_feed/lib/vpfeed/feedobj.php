<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	abstract class FeedObj extends DBAcc
	{
		protected $componentId;
		protected $instanceId;

		public function __construct($database, $componentId, $instanceId)
		{
			$this->db = $database;
			$this->componentId = $componentId;
			$this->instanceId = $instanceId;
		}

		public abstract function feed($id, $link);

		protected final function getConfig($config)
		{
			$sql = "SELECT `widget_cfgreg`.`value` FROM `widget_cfgreg` JOIN `rescast` ON `widget_cfgreg`.`type` = `rescast`.`id` ";
			$sql .= "WHERE `rescast`.`type`='Component' AND `widget_cfgreg`.`inst`='{$this->instanceId}' ";
			$sql .= "AND `widget_cfgreg`.`cid`='{$this->componentId}' AND `widget_cfgreg`.`config`='$config';";
			$r = $this->db->sendQuery($sql, false, false);
			if(!$r)
				return null;

			return $r[0]['value'];
		}
	}
?>
