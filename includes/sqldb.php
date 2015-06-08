<?php
/*
 * Administers all SQL database procedures.
 * 
 */

require_once 'errorlogs.php';

date_default_timezone_set('America/New_York');


/*
 * deletedb(arg)
 * 
 * arg = mysql db connection info
 * 
 * If customer chooses to Reset their data, this function will execute
 * It deletes both tables from the database but not the database itself
 */

function deletedb($link) {
    
    if (!$link)
        exit("Could not connect mysql: " . mysqli_error($link));
    else {
        log_action("connected successfully: mysql");
        
        
        mysqli_select_db($link, "reorder_db");
        
        if (mysqli_query($link, "DROP TABLE IF EXISTS Reorders"))
            log_action("deleted successfully: Reorders");
        else
            log_error("Error executing database query: " . mysqli_error($link));
        
        if (mysqli_query($link, "DROP TABLE IF EXISTS Classes"))
            log_action("deleted successfully: Classes");
        else
            log_error("Error executing database query: " . mysqli_error($link));
        
        if (mysqli_query($link, "DROP TABLE IF EXISTS LastUpdated"))
            log_action("deleted successfully: LastUpdated");
        else
            log_error("Error executing database query: " . mysqli_error($link));
            
    }
}

/*
 * checkdbexists(arg)
 * 
 * arg = mysql db connection info
 * 
 * Check to ensure mysql can be accessed, that the db
 * exists and that the tables exist. If the db and/or
 * tables do not exist, they are created.
 * 
 */

function checkdbexists($link) {
	
if (!$link)
	exit("Could not connect mysql: " . mysqli_error($link));
else {
	log_action("connected successfully: mysql");
        
	if (mysqli_query($link, "CREATE DATABASE IF NOT EXISTS reorder_db"))
		log_action("connected successfully: reorder_db");
	else {
		log_error("Error checking/creating reorder_db: " . mysqli_error($link));
		mysqli_close($link);
		log_action("Disconnected Successfully...");
		exit();
	}
			
	mysqli_select_db($link, "reorder_db");
	
	$sql = "CREATE TABLE IF NOT EXISTS Reorders (" .
                    "rowID int NOT NULL AUTO_INCREMENT, " .
                    "PRIMARY KEY (rowID), " .
                    "resourceID INT UNSIGNED, " .
                    "code VARCHAR(50), " .
                    "description VARCHAR(50), " .
                    "classID INT UNSIGNED, " .
                    "total INT UNSIGNED, " .
                    "available INT UNSIGNED, " .
                    "ropoint INT UNSIGNED, " .
                    "roamount INT UNSIGNED, " .
                    "reserved INT UNSIGNED, " .
                    "coming INT UNSIGNED, " .
                    "rocalc INT UNSIGNED)";
		   	
	if (mysqli_query($link, $sql))
		log_action("connected successfully: Reorders table");
        
	else {
		log_error("Error executing table check query: " . mysqli_error($link));
		mysqli_close($link);
		log_action("<br>Disconnected Successfully...");
		exit();
	}
        
        $sql = "CREATE TABLE IF NOT EXISTS Classes (" .
                    "rowID int NOT NULL AUTO_INCREMENT, " .
                    "PRIMARY KEY (rowID), " .
                    "resourceID INT UNSIGNED, ".
                    "name VARCHAR(50))";
    
        if (mysqli_query($link, $sql))
		log_action("connected successfully: Classes table");
	else {
		log_error("Error executing LastUpdated table check query: " . mysqli_error($link));
		mysqli_close($link);
		log_action("Disconnected Successfully...");                
		exit();
	}
        
        $sql = "CREATE TABLE IF NOT EXISTS LastUpdated (" .
                    "rowID int NOT NULL AUTO_INCREMENT, " .
                    "PRIMARY KEY (rowID), " .
                    "date DATETIME)";
    
        if (mysqli_query($link, $sql))
		log_action("connected successfully: LastUpdated table");
	else {
		log_error("Error executing LastUpdated table check query: " . mysqli_error($link));
		mysqli_close($link);
		log_action("Disconnected Successfully...");                
		exit();
	}
}
}

function updatedbclass($array, $link){
    if ($array)
    {
	    if (!$link)
	    {
		    exit("Could not connect. " . mysqli_errno($link) . ': ' . mysqli_error($link));
	    }
	    else
	    {
		    log_action('connected successfully: mysql');

		    mysqli_select_db($link, "reorder_db");

		    $updatequery = "UPDATE Classes SET name='".$array->name."' ".
			    "WHERE resourceID='".$array->attributes()->id."'; ".
			    "SELECT row_count();";

		    $result = array();
		    $re = mysqli_query($link, $updatequery);

		    if ($re > 0)
		    {
			    while ($result[] = mysqli_fetch_array($re));
		    }
		    elseif ($re != 0)
		    {
			    echo mysqli_errno($link) . ': ' . mysqli_error($link);
			    debug_print_backtrace();
			    log_error(mysqli_errno($link) . ': ' . mysqli_error($link));
			    exit();
		    }

		    if (!empty($result) && isset($result[0][0]) && $result[0][0] == 1)
			    log_action('Record updated successfully: Class - '.$array->name);
		    else {

			    $insertquery =  "INSERT INTO Classes (resourceID, name) ".
				    "VALUES (".$array->attributes()->id.",".
				    "'".$array->name."')";

			    if (mysqli_query($link, $insertquery))
				    log_action('Record added successfully: Class - '.$array->name);
			    else
				    log_error("Error inserting class: " . mysqli_error($link));
		    }
	    }
    }
}

/*
 * updatedbtable
 * 
 * arg - array = array of information to update in SQL db
 * 
 * Update all products with current quantities and add any new products to db
 * 
 */

function updatedbtable($array, $link){
    if ($array)
    if (!$link)
	exit("Could not connect" . mysqli_error($link));
    else {
	log_action('connected successfully: mysql');
        
        mysqli_select_db($link, "reorder_db");
        
        $rcalc = 0 + ($array->reorder->calc - ($array->inventory->coming_for_stock + $array->inventory->coming_for_customers));
        if ($rcalc < 0)
            $rcalc = 0;
        
        $updatequery = "UPDATE Reorders SET code='".$array->code."', ".
                                        "description='".str_replace("'","''",$array->description)."', ".
                                        "classID='".$array->class->attributes()->id."', ".
                                        "total='".$array->inventory->total."', ".
                                        "available='".$array->inventory->available."', ".
                                        "ropoint='".$array->reorder->point."', ".
                                        "roamount='".$array->reorder->amount."', ".
                                        "reserved='".$array->inventory->reserved."', ".
                                        "coming='".$array->inventory->coming_for_stock + $array->inventory->coming_for_customers."', ".
                                        "rocalc='".$rcalc."', ".    
                        "' WHERE resourceID='".$array->attributes()->id."'";
            
	if (mysqli_query($link, $updatequery))
            log_action('Record updated successfully: Product Code '.$array->code);

        else {
            
            $insertquery =  "INSERT INTO Reorders (resourceID, code, description, classID, total, available, ". 
                            "ropoint, roamount, reserved, coming, rocalc) ".
                            "VALUES (".$array->attributes()->id.",".
                                     "'".$array->code."',".
                                     "'".str_replace("'","''",$array->description)."',".
                                     "'".$array->class->attributes()->id."',".
                                     "'".intval($array->inventory->total)."',".
                                     "'".intval($array->inventory->available)."',".
                                     "'".intval($array->reorder->point)."',".
                                     "'".intval($array->reorder->amount)."',".
                                     "'".intval($array->inventory->reserved)."',".
                                     "'".intval($array->inventory->coming_for_stock + $array->inventory->coming_for_customers)."',".
                                     "'".intval($rcalc)."')";
                            
            if (mysqli_query($link, $insertquery))
                log_action('Record added successfully: Product Code - '.$array->code);
            else
                log_error("Error inserting record: " . mysqli_error($link));
        }
        
        
       
    }
}

/*
 * updatedbdate
 * 
 * arg = mysql connection info
 * 
 * If the user chooses to update the db with changes, 
 * the new date is added to the db
 * 
 */

function updatedbdate($link){
    
    if (!$link)
	exit("Could not connect" . mysqli_error($link));
    else {
    
        mysqli_select_db($link, "reorder_db");
        
        $datequery =    "INSERT INTO LastUpdated (date)".
                        "VALUES ('".date('Y-m-d H:i:s')."')";
        
        if (mysqli_query($link, $datequery))
            log_action("Record added successfully: Date - ".date('Y-m-d H:i:s'));
        else
            log_error("Error inserting date: " . mysqli_error($link));
    }
}


function getreorderproducts($link, $desc, $class) {
    if (!$link)
		exit("Could not connect" . mysqli_error($link));
    else {
	//echo "connected successfully: mysql<br>";
        
        mysqli_select_db($link, "reorder_db");
           
        
        if ($desc){
            if ($desc[1]=='contains')
                $dcomp = 'LIKE';
            else
                $dcomp = 'NOT LIKE';
            $q1 = " AND description ".$dcomp." '%".$desc[0]."%'";
        }
        else 
            $q1 = null;
        
        if ($class) {
            if ($class[1]=='contains')
                $ccomp = 'LIKE';
            else
                $ccomp = 'NOT LIKE';
            $tq = "SELECT resourceID FROM Classes WHERE name ".$ccomp." '%".$class[0]."%';";
            //echo $tq.'<br>';
            $res = mysqli_query($link, $tq);
	        $res1 = array();

            if ($res)
            {
	            while ($res1[] = mysqli_fetch_array($res));
            }
            else
            {
	            log_error("Error running SELECT query: ". mysqli_error($link));
            }

            $q2 = " AND (classID='".$res1[0][0]."'";
            
            $x=1;
            while (isset($res1[$x][0]))
                $q2 .= " OR classID='".$res1[$x++][0]."'";
            
            $q2 .= ")";
                
        }
        else 
            $q2 = null;
        
        $query = "SELECT * FROM Reorders WHERE ropoint >= available".$q1.$q2.";";
	    $result = null;
        //echo $query."<br><br>";
        
        $re = mysqli_query($link, $query);
        if ($re)
            while ($result[] = mysqli_fetch_array($re)) ;
        else
            log_error("Error running SELECT query: ". mysqli_error($link));
            //echo "Error running SELECT query: ". mysqli_error (). "<br><br>";
        
        return $result;
    }
}

function getdatelastupdatedb($link){

    if (!$link)
		exit("Could not connect" . mysqli_error($link));
    else {
		//echo "connected successfully: mysql<br>";

        mysqli_select_db($link, "reorder_db");
        
        $query = "SELECT date FROM LastUpdated ORDER BY rowID DESC LIMIT 1;";
        
        $re = mysqli_query($link, $query);
        if ($re)
        {
            while ($result[] = mysqli_fetch_array($re)) ;
            return $result[0]['date'];
        }
        else
        {
            log_error("Error getting date: ". mysqli_error($link));
            return 'n/a';
        }

     //mysqli_close($link);
     
    }
}

?>
