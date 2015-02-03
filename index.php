<?php
/*
 *	index.php
 *
 *	user interface page
 */
 
require_once 'includes/sqldb.php';

$link = mysql_connect('localhost', 'root', '');
$tempdate = getdatelastupdatedb($link);

if ($tempdate == 'n/a')
	$datelastupdated = $tempdate;
else
	$datelastupdated = date('D jS M Y, g:i a', strtotime($tempdate));

mysql_close($link);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>LSS API Custom Reporter - Reordering</title>
	<style>
		.serif{font-family:"Times New Roman", Times, serif;}
		.sansserif{font-family:Verdana, Geneva, sans-serif;}
		.footer{font-family:"Times New Roman", Times, serif;
				font-size:12px;}
	</style>
</head>
<script>
	function myFunction() { 
		alert("LSS Customer Reorder Reporter v1.0\n\nReturns all products whose reorder point is equal to or exceeds the available quantity\n\nWHY? LightSpeed currently compares with the total and not the available quantity."); 
	}
</script>
<body>
<form action="reorderreport.php" method="post">
	<table width="650px">
		<tr>
			<td>
				<p align="center">
				<img src="images/eos_logo.png" width="20%" height="20%"/>
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<h2 class="sansserif" align="center">LSS API Custom Reporter - Reordering</h2>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0"  width="100%">
				<tr>
					<td style="padding-right:20px; width:50%">
					<p align="right" class="sansserif"><i>database last updated</i></p>
					</td>
					<td style="padding-left:20px; width:50%">
					<p class="sansserif"><?php echo $datelastupdated; ?></p>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" width="100%">
				<tr>
					<td style="padding-right:20px; width:33%">
						<p align="right" class="sansserif"><i>product description</i></p>
					</td>
					<td>
						<p align="center">
						<select name="desc-comp">
							<option value="contains">contains</option>
							<option value="notcontain">does not contain</option>
						</select>
						</p>
					</td>
					<td style="padding-left:20px; width:33%">
						<input type="text" name="desc" maxlength="40" size="30"/>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" width="100%">
				<tr>
					<td style="padding-right:20px; width:33%">
						<p align="right" class="sansserif"><i>class</i></p>
					</td>
					<td>
						<p align="center">
						<select name="class-comp">
							<option value="contains">contains</option>
						<option value="notcontain">does not contain</option>
						</select>
						</p>
					</td>
					<td style="padding-left:20px; width:33%">
						<input type="text" name="class" maxlength="40" size="30"/>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0"  width="100%">
				<tr>
					<td style="padding-right:20px; width:33%">
					<p align="right" class="sansserif"><i>export report</i></p>
					</td>
					<td style="padding-left:43px;">
						<input type="checkbox" name="export">
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0"  width="100%">
				<tr>
					<td style="padding-right:20px; width:33%">
					<p align="right" class="sansserif"><i>update database</i></p>
					</td>
					<td style="padding-left:43px;">
						<input type="checkbox" name="update">
					</td>
				</tr>
			</table>
			</td>
		</tr>
                <tr>
			<td>
			<table border="0"  width="100%">
				<tr>
					<td style="padding-right:20px; width:33%">
					<p align="right" class="sansserif"><i>update inventory quantities</i></p>
					</td>
					<td style="padding-left:43px;">
						<input type="checkbox" name="updateinv">
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0"  width="100%">
				<tr>
					<td style="padding-right:20px; width:33%">
					<p align="right" class="sansserif"><i>reset database</i></p>
					</td>
					<td style="padding-left:43px;">
						<input type="checkbox" name="reset">
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
				<p align="center">
				<input type="submit" value="GENERATE REPORT">
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<p align="center" class="footer"><a href="#" onclick="myFunction()">About</a> | Support | Help</p>
			</td>
		</tr>
	</table>
</form>
</body>
</html>