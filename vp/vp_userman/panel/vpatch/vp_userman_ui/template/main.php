
<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
echo "<div class=\"vp-gm-container\" id=\"{$vars->_pmod}-container\">";
	echo "<div class=\"sidebar\">";
	echo "<div class=\"header\">Users</div>";
	echo "<div id=\"{$vars->_pmod}-grouplist\" class=\"scroller\">";
	if($vars->users) {
		foreach($vars->users as $u) {
			echo "<div class=\"item\">";
			echo "{$u['label']}";
			echo "</div>";
		}
	}
	echo "<div class=\"item\">";
	echo "<a href=\"javascript:void(0);\" id=\"{$vars->_pmod}-nusr\">New User</a>";
	echo "</div>";
	echo "</div>";
	echo "</div>";
	echo "<div class=\"details\">";
		echo "<div class=\"header\">";
			echo "User: <span id=\"{$vars->_pmod}-gname\">- - -</span>";
		echo "</div>";
		echo "<div class=\"vform-item-spacer\"  id=\"{$vars->_pmod}-details\">";
		echo "<div class=\"list\"></div>";
		echo "</div>";
	echo "</div>";
echo "</div>";
?>
