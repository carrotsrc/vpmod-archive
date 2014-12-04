<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
 ?>
<div class="manager-title" style="padding-bottom: 5px;" style="display: block;">
<?php echo $vars->blog[0][1]; ?>
</div>
<div class="manager-area" style="display: block; padding-right: 15px;">
<?php 

	foreach($vars->posts as $post) {
		echo "<div style=\"margin-bottom: 30px;\">";
		echo "<div class=\"vfont-large\">";
		$title = stripslashes($post[3]);
		echo "<b>{$title}</b><br />";
		echo "<div class=\"vfont-small\">";
		echo "{$post[5]}";
		echo "</div>";
		echo "</div>";
		echo "<div class=\"vform-item-spacer\">";
		$body = stripslashes($post[4]);
		echo str_replace("\n", "<br />", $body);
		echo "</div>";

		echo "</div>";
	}
?>
</div>
