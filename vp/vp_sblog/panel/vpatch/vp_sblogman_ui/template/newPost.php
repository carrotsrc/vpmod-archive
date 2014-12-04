<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
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

		echo "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
	}

	echo "<select name=\"vpbbid\" class=\"vform-text vform-select\">";
	foreach($vars->blogs as $b) {
		echo "<option value=\"{$b[0]['id']}\">{$b[0]['title']}</option>";
	}
	echo "<option value=\"0\">Create new Blog</option>";
	echo "</select>";
	echo " <input type=\"submit\" value=\"go\" class=\"vform-button\" />";
?>
</form>
</div>
</div>

<div class="manager-area">
<div class="cat-title">New Post</div>
<div class="vfont-small" style="font-weight: bold;"><?php
foreach($vars->blogs as $b) {
	if($b[0]['id'] == $vars->vpid)
		echo $b[0]['title'];
}
?>
</div>

<form method="post" enctype="multipart/form-data" action="<?php echo $vars->_fallback->add; ?>">
<div class="vform-item-spacer">
<input type="hidden" name="vpbb" value="<?php echo $vars->vpid; ?>" /> 
<input type="text" name="vpbt" class="vfont-x-large vform-text" />
</div>

<div class="vform-item-spacer">
<textarea name="vpbc" cols="65" rows="13" class="vform-text" style="width: 100%;"></textarea>
</div>
<div class="vform-item-spacer">
<?php
if($vars->vatt) {
?>
<a style="color: #808080; text-decoration: none;" href="<?php echo $vars->_fallback->vatt; ?>">attachments</a>
<div style="border-top: 1px solid #D8D8D8; margin-bottom: 7px; padding: 5px;">
	<div class="" style="padding: 5px;">
		Available Attachments:<br />
		<?php
			if($vars->attachments != null) {
				foreach($vars->attachments as $att) {
					echo "<div style=\"text-align: center; display: table-cell;  border: 0px solid red;\">";
					if($att['name'] == "Img") {
						echo "<img height=\"48px\" src=\"{$att['url']}\" title=\"Ref: {$att['id']}\" alt=\"{$att['title']} ({$att['id']})\" />";
					}
					echo "<br /><input type=\"checkbox\" name=\"ainc{$att['id']}\" value=\"{$att['id']}\">";
					echo "</div>";
				}
			}
			else
				echo "( none )";
		?>
	</div>
	<div class="vform-item-spacer" style="border-top: 2px dashed #d8d8d8; padding: 5px; padding-top: 10px;">
		<br />
		Upload new attachment:
			<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
			<input type="hidden" name="vpbb" value="<?php echo $vars->vpid; ?>" /> 
			<input class="vform-button" name="vpbu" type="file" />
			<input class="vform-button" type="submit" value="upload" />
	</div>
</div>
<?php
}
else {
?>
<div style="border-bottom: 1px solid #D8D8D8;">
<a style="color: #D8D8D8; text-decoration: none;" href="<?php echo $vars->_fallback->vatt; ?>&vpbva=1">attachments</a>
</div>
<?php
}
?>
</div>

<div class="vform-item-spacer">
	<input type="submit" class="vform-button" value="Add Post" />
</div>
</form>

</div>
