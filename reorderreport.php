<?php

/*
 * reorderreport.php
 *
 * Essentially handles all the API related requests. 
 * It checks the user input form variables and behaves accordingly
 */

require_once 'includes/rest_connector2.php'; 
require_once 'includes/session.php';
require_once 'includes/sqldb.php';

date_default_timezone_set('America/New_York');

$timestarted = time();

// check to see if we start a new session or maintain current one
checksession();

$rest = new RESTConnector();
$createtabledb = true;
$link = mysqli_connect('localhost', 'root', '');

/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$urlp = "products/?filter=";
$urlc = "setup/classes/";
$filter = '(reorder_amt > 0)';

// Reset the external db
if (isset($_POST['reset']))
{
    deletedb($link);
    $_POST['update']=false;
    $_POST['updateinv']=false;
}

// Check to ensure db external db exists
checkdbexists($link);

// An update of inventory requires a parse of the entire product database. 
// The same is true if the db was reset
if (isset($_POST['updateinv']))
{
    $_POST['reset']=true;
    $_POST['update']=false;
}

// An update of recently modified products requires a relevant filter        
if (isset($_POST['update']))
{
    $datelu = getdatelastupdatedb($link);
    
    if ($datelu != 'n/a' && $datelu != '')
        $filter = '(reorder_amt > 0 AND datetime_mod > "'.$datelu.'")';
    else
        $filter = '(reorder_amt > 0)';
}
else
    $filter = '(reorder_amt > 0)';


// GET all products under applicable $filter
if (isset($_POST['reset']) || isset($_POST['update']))
{
	$productarray = array();
	$classarray = array();

	$rest->createRequest($urlp . rawurlencode($filter), "GET", null, $_SESSION['cookies']);
	$rest->sendRequest();
	$response = $rest->getResponse();
	$error = $rest->getError();
	$exception = $rest->getException();

	// save our session cookies
	if ($_SESSION['cookies']==null)
		$_SESSION['cookies'] = $rest->getCookies();

	// display any error message
	if ($error!=null)
		die($error);

	if ($exception!=null)
		die($exception);

	$temp = null;

	if ($response!=null || $response!="") {

		$temp = simplexml_load_string($response);
		$x=0;
		while ($temp->product[$x]){
			$productarray[$x] = $temp->product[$x]->attributes()->id;
			$x++;
		}
		//echo 'productarray: '.$x.'<br>';
}
else
	echo "There was no response.";


// GET all classes
$z=$y=0; 
$rest->createRequest($urlc,"GET", null, $_SESSION['cookies']);
$rest->sendRequest();
$response = $rest->getResponse();
$error = $rest->getError();
$exception = $rest->getException();

// save our session cookies
if ($_SESSION['cookies'] == null)
	$_SESSION['cookies'] = $rest->getCookies();

// display any error message
if ($error!=null)
	die($error);

if ($exception!=null)
	die($exception);

$temp = null;

if ($response!=null || $response=="")
{
    $temp = simplexml_load_string($response);
    $x=0;
    while ($temp->class[$x])
    {
        updatedbclass($temp->class[$x], $link);
        $x++;
    }
}
else
	echo "There was no response.";

// GET full product render and update product in external db
foreach ($productarray as $rid)
{
	$url = 'products/'.$rid.'/';
	$rest->createRequest($url,"GET", null, $_SESSION['cookies']);
	$rest->sendRequest();
	$response = $rest->getResponse();
	$error = $rest->getError();
	$exception = $rest->getException();

	// save our session cookies
	if ($_SESSION['cookies'] == null)
		$_SESSION['cookies'] = $rest->getCookies();

	// display any error message
	if ($error!=null)
		echo $error;

	// display any error message
	if ($exception!=null)
		echo $exception;

	if ($response!=null || $response=="") {

		$temp = simplexml_load_string($response);
		updatedbtable($temp, $link);

	}
	else
		echo "There was no response.";
}
// END FOREACH Update products in external db

// Add new updated date to external db
updatedbdate($link);
}

// ENDIF Reset|Update

// BEGIN Display report on screen

// Save user input filters
$desc = array(0=>$_POST['desc'], 1=>$_POST['desc-comp']);
$class1 = array(0=>$_POST['class'], 1=>$_POST['class-comp']);

$roproductarray = getreorderproducts($link, $desc, $class1);

// Export Report
if (isset($_POST['export']))
{
	if (!file_exists('export')) {
		@mkdir('export');
	}

    $folder = 'export/';
    $file = 'reorder-export-'.date('Ymd-His').'.txt';
    $textfile = $folder.$file;
    
    $tab = "\t"; $newline = "\r";
         
    if (!$handle = fopen($textfile, 'a')) 
    {
	    log_error('Cannot open file: '.$textfile);
    }
	else
	{
		if (fwrite(
				$handle,
				'Product Code'.utf8_encode($tab).
				'Description'.utf8_encode($tab).
				'Total Qty'.utf8_encode($tab).
				'On Hand'.utf8_encode($tab).
				'RO Point'.utf8_encode($tab).
				'Reserved'.utf8_encode($tab).
				'On Order'.utf8_encode($tab).
				'RO Qty'.utf8_encode($newline)
			) === false
		)
		{
			log_error('Cannot write to file: '.$file);
		}

		$count = 0;
		foreach ($roproductarray as $roproduct)
		{
			if (fwrite(
					$handle,
					$roproduct['code'].utf8_encode($tab).
					$roproduct['description'].utf8_encode($tab).
					$roproduct['total'].utf8_encode($tab).
					$roproduct['available'].utf8_encode($tab).
					$roproduct['ropoint'].utf8_encode($tab).
					$roproduct['reserved'].utf8_encode($tab).
					$roproduct['coming'].utf8_encode($tab).
					$roproduct['rocalc'].utf8_encode($newline)
				) === false
			)
			{
				log_error('Cannot write to file: '.$file);
				break;
			}

			$count++;
		}

		if ($count == count($roproductarray))
		{
			log_action($count . ' rows added to file: '.$file);
		}

		echo 'Exported file: <b>'.$file.'</b><br><br>';
	}

    fclose($handle);
}

?>

<!DOCTYPE html>
<html lang="en">
<title>LSS Custom Reorder Report v2.0</title>
<head>
	<link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href='assets/reorderreport.css' rel='stylesheet' type='text/css'>
</head>
<body>
   <table>
        <tr width="800px">
            <td width="600px"><h1>API Product Reorder Report</h1></td>
            <td align="right">Last update: <?php echo getdatelastupdatedb($link); ?></td>
        </tr>
    </table>    
    <table class="table table-hover">
	    <thead>
	    <th>Code</th>
	    <th>Description</th>
	    <th>Total Qty</th>
	    <th>On Hand</th>
	    <th>RO Point</th>
	    <th>Reserved</th>
	    <th>On Order</th>
	    <th>RO Qty</th>
	    </thead>
	    <tbody>
	    <?php foreach ($roproductarray as $roproduct): ?>
	    <tr>
		    <td><?php echo $roproduct['code']; ?></td>
		    <td><?php echo $roproduct['description']; ?></td>
		    <td><?php echo $roproduct['total']; ?></td>
		    <td><?php echo $roproduct['available']; ?></td>
		    <td><?php echo $roproduct['ropoint']; ?></td>
		    <td><?php echo $roproduct['reserved']; ?></td>
		    <td><?php echo $roproduct['coming']; ?></td>
		    <td><?php echo $roproduct['rocalc']; ?></td>
        </tr>
        <?php endforeach; ?>
	    </tbody>
    </table>

    <?php
    mysqli_close($link);
    $timeended = time();
    echo '<br>Elapsed time: ';
    echo $timeended-$timestarted;
    echo ' seconds'; 
    
    // END display report on screen
    ?>
    
</body>
</html>