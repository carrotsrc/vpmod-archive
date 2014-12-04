<?php
/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

	if($vars->post == null) {
		return;
	}

	$title = StrSan::mysqlDesanatize($vars->post[3]);
	$title = StrSan::htmlSanatize($title);
	$content = StrSan::mysqlDesanatize($vars->post[4]);
	$content = StrSan::htmlSanatize($content);
	echo "<div class=\"blog-title-gap\"></div>";
	echo "<div class=\"blog-title\">";
	echo $title;
	echo "</div>";
?>

<div class="blog-container">
<?php
	echo "<div class=\"blog-content\">";
	echo $content;
	echo "</div>";
?>
</div>
