<?php
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $vars->app->title; ?> System</title>
<link href="assets/vp03dark.css" type="text/css" rel="stylesheet" />
<?php
	foreach($vars->assets['css'] as $a)
		echo "$a\n";
	foreach($vars->assets['js'] as $a)
		echo "$a\n";
?>
<script>
	window.onload = function () {
<?php
	if(!$vars->nodym)
		echo $vars->onload;
?>
	}
</script>
</head>
<body>

<div id="page-container">
	<div id="page-header" class="vpc-singlecol bottom-rounded">
	<div class="header-container">
		<img style="float: right; height: 125px; margin-right: 50px; margin-top: -10px;" src="<?php echo $vars->media; ?>/carrot.png">
		<h1><?php echo $vars->app->title; ?>
		</h1>
	</div>
	</div>

	<div id="page-content">
<?php
	echo $vars->app->layout;
?>
	</div>

	<div id="page-footer">

	</div>
</div>

</body>
</html>
