<?php ?>
<!DOCTYPE html>
<html lang="en">
<title>Access Configuration</title>
<head>
	<link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href='assets/reorderreport.css' rel='stylesheet' type='text/css'>
</head>
<body>
<h1>Access Configuration</h1>
<div class="alert alert-info" role="alert">Check <b>Remember me</b> to skip this step the next time.</div>
<form class="form-horizontal" method="post" action="config/saveconfig.php" id="configform">
	<label class="section">Lightspeed Onsite</label>
	<div class="form-group">
		<label for="lightspeedUser" class="col-sm-2 control-label">Username <span class="req">*</span></label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="lightspeedUser" name="lightspeedUser" required="true" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="lightspeedPass" class="col-sm-2 control-label">Password <span class="req">*</span></label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="lightspeedPass" name="lightspeedPass" required="true" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="lightspeedServer" class="col-sm-2 control-label">Server</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="lightspeedServer" name="lightspeedServer" placeholder="localhost">
		</div>
	</div>

	<div class="form-group">
		<label for="lightspeedPort" class="col-sm-2 control-label">Port</label>
		<div class="col-sm-10">
			<input type="number" class="form-control" id="lightspeedPort" name="lightspeedPort" placeholder="9630">
		</div>
	</div>

	<div class="seperator"></div>

	<label class="section">mySQL</label>

	<div class="form-group">
		<label for="mysqlUser" class="col-sm-2 control-label">Username</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mysqlUser" name="mysqlUser" placeholder="root">
		</div>
	</div>

	<div class="form-group">
		<label for="mysqlPass" class="col-sm-2 control-label">Password</label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="mysqlPass" name="mysqlPass" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="mysqlHost" class="col-sm-2 control-label">Server</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="mysqlHost" name="mysqlHost" placeholder="localhost">
		</div>
	</div>

	<div class="form-group">
		<label for="mysqlPort" class="col-sm-2 control-label">Port</label>
		<div class="col-sm-10">
			<input type="number" class="form-control" id="mysqlPort" name="mysqlPort" placeholder="3306">
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-10">
			<div class="checkbox">
				<label>
					<input type="checkbox" id="remember" name="remember"> Remember me
				</label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-10">
			<button type="submit" name="submit" class="btn btn-default">Submit</button>
		</div>
	</div>
</form>

</body>
</html>