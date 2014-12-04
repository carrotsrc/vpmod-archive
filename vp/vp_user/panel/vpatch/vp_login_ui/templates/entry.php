<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
if($vars->successful == null) {
?>
<form method="post" action="<?php echo $vars->_fallback->login?>">
	<div class="vfont-x-large" style="color: #808080;">
		<b>username:</b><br />
		<input name="uname" type="text" class="vform-text vfont-x-large" value="" /><br />
		<div class="vform-item-spacer">
		<b>password:</b><br />
		<input name="upass" type="password" class="vform-text vfont-x-large" value="" style=""/><br />
		</div>
	</div>

	<input type="submit" value="login" style="float: left; margin-top: 10px; margin-right: 15px;" class="login-form-button login-font-x-large">
	<?php if($vars->err != null) {
		echo "<div style=\"margin-top: 17px;\">Incorrect login</div>";
	}?>
</form>
<?php
} else {
?>
	<div class="login-font-x-large">
		<b><?php echo $vars->username; ?></b><br />
		Successfully logged in!
	</div>
<?php
}
?>

</div>
