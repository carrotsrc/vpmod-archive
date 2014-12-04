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

<form method="get" action="<?php echo $vars->_fallback->change; ?>">
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

<div class="manager-area" style="display: block;">
<div style="font-size: large;">
<b><?php
foreach($vars->blogs as $b) {
	if($b[0][0] == $vars->vpid)
		echo $b[0][1];
}
?></b>
</div>
<div>
<br />
<?php
if(!$vars->instman) {
	echo "<div class=\"vform-item-spacer\">";

	echo "<div class=\"separate vform-item\">";
	echo "<b>Posts</b> | <a class=\"a-light\" href=\"{$vars->_fallback->chmd}&vpbv=1\">Viewers</a>";
	echo "</div>";
	echo "</div>";
	if($vars->posts != null) {
		foreach($vars->posts as $p) {
			echo "<div class=\"separate vform-item\">";

			echo "<div class=\"cat-title\">";
			echo "<a class=\"a-light\" href=\"{$vars->_fallback->edit}&vpbpi={$p[3]}\">";
			echo "{$p[1]}";
			echo "</a>";
			echo "</div>";

			echo "<div class=\"vfont-small\">{$p[2]}</div>";
			
			echo "</div>";
		}
	}
	else {
		echo "No posts";
	}

	echo "</div>";

	echo "<div class=\"vform-item-spacer\">";
	echo "<a href=\"{$vars->_fallback->newpost}&vpba=1\"><b>New Post</b></a>";

	echo "</div>";
}
else {
	echo "<div class=\"vform-item-spacer\">";

	echo "<div class=\"separate vform-item\">";
	echo "<a class=\"a-light\" href=\"{$vars->_fallback->chmd}\">Posts</a> | <b>Viewers</b>";
	echo "</div>";
	echo "</div>";
	if(sizeof($vars->instances) > 0) {
		foreach($vars->instances as $p) {
			echo "<div class=\"separate vform-item\">";
			echo "<div class=\"cat-title\">";
			echo "{$p['label']} =&gt; {$p['handler']}";
			echo "</div>";

			echo "<div class=\"vfont-small\">{$p['id']}</div>";
			
			echo "</div>";
		}
	}
	else {
		echo "No instances";
	}

	echo "</div>";

	echo "<div class=\"vform-item-spacer\">";
	echo "New Viewer";
	echo "<form method=\"post\" action=\"{$vars->_fallback->addview}\">";
	echo "<input type=\"hidden\" name=\"vpbb\" value=\"{$vars->vpid}\" />"; 
	echo "<input type=\"text\" class=\"vform-text vform-item\" name=\"vpbin\" /><br />";
	echo "<input type=\"submit\" class=\"vform-button vform-item\" value=\"create\" />";
	echo "</form>";
	echo "</div>";
}

?>
</div>
</div>
