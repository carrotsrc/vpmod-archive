<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
?>
<div class="register">
<div class="rform">

	<?php
		if($vars->key == null || $vars->activated == false) {
	?>
		<center style="color: #cd0000; font-weight: bold;">
		<img src="<?php echo $vars->_fallback->mediag; ?>/ncross-128.png" /><br /><br />
		Error occurred!
		</center>
		<div style="font-size: small; text-align: center; margin-top: 50px; color: #cd0000;">
		Sorry, an error occured on the activation. Please report the issue to administration.
		</div>
	<?php
		}
		else {
	?>
		<center style="color: #7D9E05; font-weight: bold;">
		<img src="<?php echo $vars->_fallback->mediag; ?>/ngtick-128.png" /><br /><br />
		Successfully activated!
		</center>
		<div style="font-size: small; text-align: center; margin-top: 50px;">
		Your can now <a href="index.php" style="text-decoration: underline;">login</a> with your account!
		</div>
	<?php
		}
	?>
</div>

</div>
