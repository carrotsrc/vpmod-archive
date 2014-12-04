<?php
echo "<div class=\"vp-gm-container\" id=\"{$vars->_pmod}-container\">";

/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
	echo "<div class=\"sidebar\">";
	echo "<div class=\"header\">Groups</div>";
	echo "<div id=\"{$vars->_pmod}-grouplist\" class=\"scroller\">";
	if($vars->groups) {
		foreach($vars->groups as $g) {
			echo "<div class=\"item\">";
			echo $g[1];
			echo "</div>";
		}
	}
		echo "<div class=\"item\">";
		echo "<a href=\"javascript:void(0);\" id=\"{$vars->_pmod}-ngrp\">New Group</a>";
		echo "</div>";
	echo "</div>";
	echo "</div>";
	echo "<div class=\"details\">";
		echo "<div class=\"header\">";
			echo "Group: <span id=\"{$vars->_pmod}-gname\">- - -</span>";
		echo "</div>";
		echo "<div class=\"vform-item-spacer\"  id=\"{$vars->_pmod}-details\">";
		echo "<div class=\"list\"></div>";
		echo "</div>";
	echo "</div>";
echo "</div>";
?>
