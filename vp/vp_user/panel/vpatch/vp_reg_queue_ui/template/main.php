<?php
/* Copyright 2014, Zunautica Initiaves Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
 ?>
<div class="manager-title">
Registration Queue
</div>
<div class="registration-queue">
<div class="content">
<?php
	echo "<table>";
	echo "<tr class=\"title\">";
		echo "<td>id</td>";
		echo "<td>username</td>";
		echo "<td>email</td>";
	echo "</tr>";
	if($vars->queue && $vars->queue != 104) {
		foreach($vars->queue as $k => $u) {
			$crow = "content-row";
			if($k%2 == 0)
				$crow .= " content-row-alt";

			echo "<tr class=\"$crow\">";
				echo "<td>";
					echo "<a href=\"{$vars->_fallback->activate}&rqkey={$u['actkey']}\">";
					echo $u['id'];
					echo "</a>";
				echo "</td>";

				echo "<td>";
					echo "<a href=\"{$vars->_fallback->activate}&rqkey={$u['actkey']}\">";
					echo $u['username'];
					echo "</a>";
				echo "</td>";

				echo "<td>";
					echo "<a href=\"{$vars->_fallback->activate}&rqkey={$u['actkey']}\">";
					echo $u['email'];
					echo "</a>";
				echo "</td>";
			echo "</tr>";
		}
	}
	else {
		echo "<tr class=\"content-row\"><td colspan=\"3\">No one in queue</td></tr>";
	}

		echo "</table>";
?>
</div>
</div>
