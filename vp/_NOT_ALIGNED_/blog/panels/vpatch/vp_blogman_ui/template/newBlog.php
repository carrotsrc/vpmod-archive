<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
 ?>
<div class="manager-title" style="padding: 5px;">
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
<b>Create New Blog</b>
<br />
<br />
<form method="post" action="<?php echo $vars->_fallback->add; ?>">
<div class="">
Title:
</div>
<div>
<input name="vpbt" type="text" class="vform-text vfont-x-large" /> <br />
<input type="submit" class="vform-button vform-item" value="Create" />

</div>
</form>
</div>
