<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
?>
<div class="register">
<div class="rform vfloat-left">
	<form action="<?php echo $vars->_fallback->submit; ?>" method="post">
	<table>
	<tr>
		<td>username </td>
	</tr>
	<tr>
		<td>
			<input name="uruser" type="text" class="vform-text vfont-x-large" autocomplete="off"/>
		</td>
	</tr>

	<tr class="tr-info">
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>

	<tr>
	<tr>
		<td>password</td>
	</tr>
		<td>
			<input name="urpass" type="password" class="vform-text vfont-x-large" autocomplete="off"/>
		</td>
	</tr>
	<tr class="tr-info">
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>

	<tr>
		<td>email</td>
	</tr>
	<tr>
		<td>
			<input name="uremail" type="text" class="vform-text vfont-x-large" autocomplete="off"/>
		</td>
	</tr>
	<tr class="tr-info">
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>

	</table>
	<input type="submit" class="vform-button vfont-large" value="Register" style="width: 50%;">
	</form>
	<div class="error">
	<?php
		if($vars->error != null) {
			switch($vars->error) {
			case 1:
				echo "*You missed entering some data.";
			break;

			case 2:
				echo "*Sorry, your email is invalid";
			break;

			case 3:
				echo "*Sorry, your username is already in use";
			break;
			}
		}
	?>
	</div>
</div>

</div>
