<?php

if (isset($_POST['submit']))
{
	$strtosave = sprintf(
		"<?php
		return array(
		'lightspeedUser' => '%s',
		'lightspeedPass' => '%s',
		'lightspeedServer' => '%s',
		'lightspeedPort' => %d,
		'mysqlUser' => '%s',
		'mysqlPass' => '%s',
		'mysqlHost' => '%s',
		'mysqlPort' => '%s',
		'remember' => %d
		);",
		$_POST['lightspeedUser'],
		$_POST['lightspeedPass'],
		$_POST['lightspeedServer'] != '' ? $_POST['lightspeedServer'] != '' : 'localhost',
		$_POST['lightspeedPort'] != '' ? $_POST['lightspeedPort'] : 9630,
		$_POST['mysqlUser'] != '' ? $_POST['mysqlUser'] : 'root',
		$_POST['mysqlPass'],
		$_POST['mysqlHost'] != '' ? $_POST['mysqlHost'] : 'localhost',
		$_POST['mysqlPort'] != '' ? $_POST['mysqlPort'] : '',
		isset($_POST['remember']) ? true : false
	);

	$fp = fopen('main.php', 'w');
	if ($fp === false)
		die("Error, can't write file config/main.php");

	fwrite($fp, $strtosave);
	fclose($fp);

	$redirectUrl = $_SERVER['HTTP_REFERER'].'reportform.php';
}
?>

<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="refresh" content="1;url=<?= $redirectUrl ?>">
	<script type="text/javascript">
		window.location.href = "<?= $redirectUrl ?>"
	</script>
	<title>Page Redirection</title>
</head>
<body>
If you are not redirected automatically, follow the <a href='<?= $redirectUrl ?>'>link to example</a>
</body>
</html>