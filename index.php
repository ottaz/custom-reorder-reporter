<?php

if (file_exists('config/main.php'))
{
	$config = require_once('config/main.php');
	if ($config['remember'] == true)
		include 'reportform.php';
	else
		include 'configform.php';
}
else
	include 'configform.php';