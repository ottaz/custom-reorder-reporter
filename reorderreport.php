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
$link = mysql_connect('localhost', 'root', '');

$urlp = "https://localhost:9630/api/products/?filter=";
$urlc = "https://localhost:9630/api/classes/";
$filter = '(reorder_amt > 0)';

// Reset the external db
if ($_POST['reset']){
    deletedb($link);
    $_POST['update']=false;
    $_POST['updateinv']=false;
}

// Check to ensure db external db exists
checkdbexists($link);

// An update of inventory requires a parse of the entire product database. 
// The same is true if the db was reset
if ($_POST['updateinv']){
    $_POST['reset']=true;
    $_POST['update']=false;
}

// An update of recently modified products requires a relevant filter        
if ($_POST['update']) {
    $datelu = getdatelastupdatedb($link);
    
    if ($datelu!='n/a')
        $filter = '(reorder_amt > 0 AND datetime_mod > "'.$datelu.'")';
    else
        $filter = '(reorder_amt > 0)';
}
else
    $filter = '(reorder_amt > 0)';


// GET all products under applicable $filter
if ($_POST['reset'] || $_POST['update']){

    
$productarray = array();
$classarray = array();

$rest->createRequest($urlp . rawurlencode($filter),"GET", null, $_SESSION['cookies'][0]);
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
$rest->createRequest($urlc,"GET", null, $_SESSION['cookies'][0]);
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

if ($response!=null || $response=="") {
    
    $temp = simplexml_load_string($response);
    $x=0;
    while ($temp->class[$x]){
        updatedbclass($temp->class[$x], $link);
        $x++;
    }
}
else
	echo "There was no response.";


// GET full product render and update product in external db
while ($productarray[$z]){
    
$url = 'https://localhost:9630/api/products/'.$productarray[$z].'/';
$rest->createRequest($url,"GET", null, $_SESSION['cookies'][0]);
$rest->sendRequest();
$response = $rest->getResponse();
$error = $rest->getError();
$exception = $rest->getException();

// save our session cookies
if ($_SESSION['cookies']==null) 
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
$z++;
}
// ENDWHILE Update products in external db

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
if ($_POST['export']){
    
    $folder = 'export/';
    $file = 'reorder-export-'.date('Ymd-His').'.txt';
    $textfile = $folder.$file;
    
    $tab = "\t"; $newline = "\r";
         
    if (!$handle = fopen($textfile, 'a')) 
            log_error('Cannot open file: '.$textfile);
    
    if (fwrite($handle, 'Product Code'.utf8_encode($tab).
                    'Description'.utf8_encode($tab).
                    'Total Qty'.utf8_encode($tab).
                    'On Hand'.utf8_encode($tab).
                    'RO Point'.utf8_encode($tab).
                    'Reserved'.utf8_encode($tab).
                    'On Order'.utf8_encode($tab).
                    'RO Qty'.utf8_encode($newline)) === false)
       log_error('Cannot write to file: '.$file);

    $x=0;
    
    while ($roproductarray[$x]){
        if (fwrite($handle, $roproductarray[$x]['code'].utf8_encode($tab).
                        $roproductarray[$x]['description'].utf8_encode($tab).
                        $roproductarray[$x]['total'].utf8_encode($tab).
                        $roproductarray[$x]['available'].utf8_encode($tab).
                        $roproductarray[$x]['ropoint'].utf8_encode($tab).
                        $roproductarray[$x]['reserved'].utf8_encode($tab).
                        $roproductarray[$x]['coming'].utf8_encode($tab).
                        $roproductarray[$x]['rocalc'].utf8_encode($newline)) === false)
            log_error('Cannot write to file: '.$file);
    $x++;
    }

    log_action($x . ' rows added to file: '.$file);

    fclose($handle);
    
    echo 'Exported file: <b>'.$file.'</b><br><br>';
}

$y=0;

?>

<html>
<title>LSS Custom Reorder Report v1.0</title>
<body>
   <table>
        <tr width="800px">
            <td width="600px"><h1>API Product Reorder Report</h1></td>
            <td align="right">Last update: <?php echo getdatelastupdatedb($link); ?></td>
        </tr>
    </table>    
    <table>
        <tr style="background-color: #b0b0b0;">
            <td>Code&nbsp;&nbsp;</td>
            <td>Description&nbsp;&nbsp;</td>
            <td>Total Qty&nbsp;&nbsp;</td>
            <td>On Hand&nbsp;&nbsp;</td>
            <td>RO Point&nbsp;&nbsp;</td>
            <td>Reserved&nbsp;&nbsp;</td>
            <td>On Order&nbsp;&nbsp;</td>
            <td>RO Qty&nbsp;&nbsp;</td>
        </tr>
        <?php while ($roproductarray[$y]){ 
             if ($y % 2 == 1) 
                 echo '<tr width="800px" style="background-color: #ebebeb;">';
             else 
                 echo '<tr width="800px">'; ?>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['code']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['description']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['total']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['available']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['ropoint']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['reserved']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y]['coming']; ?></td>
        <td style="padding-right:20px;"><?php echo $roproductarray[$y++]['rocalc']; ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php mysql_close($link);
    $timeended = time();
    echo '<br>Elapsed time: ';
    echo $timeended-$timestarted;
    echo ' seconds'; 
    
    // END display report on screen
    ?>
    
</body>
</html>