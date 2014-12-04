
<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

echo "<div class=\"vp-ud-container\" id=\"{$vars->_pmod}-container\">";

	echo "<div class=\"sidebar\">";
	echo "<div class=\"header\">Users</div>";
	echo "<div id=\"{$vars->_pmod}-userlist\" class=\"scroller\">";
	if($vars->user) {
		foreach($vars->users as $u) {
			echo "<div class=\"item\">";
			echo $u[3];
			echo " ";
			echo $u[4];
			echo " ({$u[2]})";
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
