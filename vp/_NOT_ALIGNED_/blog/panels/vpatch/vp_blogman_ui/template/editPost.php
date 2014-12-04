<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
 ?>
<div class="manager-title" style="padding-bottom: 5px;">
Blog Manager
<div class="vfloat-right" style="margin-bottom: 5px; overflow: auto;">
<form method="get" action"<?php echo $vars->_fallback->change; ?>">
<?php
	foreach($_GET as $k => $v) {
		if($k == "vpbbid")
			continue;
		else
		if($k == "vpba")
			continue;
		else
		if($k == "vpbpi")
			continue;

		echo "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
	}

	echo "<select name=\"vpbbid\" class=\"vform-text vform-select\">";
	foreach($vars->blogs as $b) {
		echo "<option value=\"{$b[0][0]}\">{$b[0][1]}</option>";
	}
	echo "<option value=\"0\">Create new Blog</option>";
	echo "</select>";
	echo " <input type=\"submit\" value=\"go\" class=\"vform-button\" />";
?>
</form>
</div>
</div>

<div class="manager-area">
<div class="cat-title">Edit Post</div>
<div class="vfont-small" style="font-weight: bold;"><?php
foreach($vars->blogs as $b) {
	if($b[0][0] == $vars->vpid)
		echo $b[0][1];
}
?>
</div>

<form method="post" action="<?php echo $vars->_fallback->update; ?>">
<div class="vform-item-spacer">
<input type="hidden" name="vpbb" value="<?php echo $vars->vpid; ?>" /> 
<input type="hidden" name="vpbi" value="<?php echo $vars->post_id; ?>" /> 
<input type="text" name="vpbt" class="vfont-x-large vform-text" value="<?php echo $vars->post[3]; ?>" />
</div>

<div class="vform-item-spacer">
<textarea name="vpbc" cols="65" rows="13" class="vform-text"><?php echo $vars->post[4]; ?></textarea>
</div>

<div class="vform-item-spacer">
<input type="submit" class="vform-button" value="Update Post" />
</div>
</div>
</form>
