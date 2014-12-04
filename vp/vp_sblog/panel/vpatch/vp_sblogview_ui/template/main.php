<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
if($vars->posts == null) {
	echo "<span style=\"font-size: 20px; font-weight: bold; color: #FF0000;\">Panel communication problem</span>";
	return;
}
 ?>
<div class="manager-title" style="padding-bottom: 5px;" style="display: block;">
<?php echo $vars->blog[0]['title']; ?>
</div>
<div class="manager-area" style="display: block; padding-right: 15px;">
<?php 
	foreach($vars->posts as $post) {
		echo "<div style=\"margin-bottom: 30px;\">";
		echo "<div class=\"vfont-large\">";
		$title = $post['title'];
		echo "<b>{$title}</b><br />";
		echo "<div class=\"vfont-small\">";
		echo "{$post['posted']}";
		echo "</div>";
		echo "</div>";
		echo "<div class=\"vform-item-spacer\">";
		echo $post['contents'];
		echo "</div>";

		echo "</div>";
	}
?>
</div>
