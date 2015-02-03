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
        exit("Could not connect mysql: " . mysql_error());
    else {
        log_action("connected successfully: mysql");
        
        
        mysql_select_db("reorder_db", $link);
        
        if (mysql_query("DROP TABLE IF EXISTS Reorders", $link))
            log_action("deleted successfully: Reorders");
        else
            log_error("Error executing database query: " . mysql_error());
        
        if (mysql_query("DROP TABLE IF EXISTS Classes", $link))
            log_action("deleted successfully: Classes");
        else
            log_error("Error executing database query: " . mysql_error());
        
        if (mysql_query("DROP TABLE IF EXISTS LastUpdated", $link))
            log_action("deleted successfully: LastUpdated");
        else
            log_error("Error executing database query: " . mysql_error());
            
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
	exit("Could not connect mysql: " . mysql_error());
else {
	log_action("connected successfully: mysql");
        
	if (mysql_query("CREATE DATABASE IF NOT EXISTS reorder_db", $link))
		log_action("connected successfully: reorder_db");
	else {
		log_error("Error checking/creating reorder_db: " . mysql_error());
		mysql_close($link);
		log_action("Disconnected Successfully...");
		exit();
	}
			
	mysql_select_db("reorder_db", $link);
	
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
		   	
	if (mysql_query($sql,$link))
		log_action("connected successfully: Reorders table");
        
	else {
		log_error("Error executing table check query: " . mysql_error());
		mysql_close($link);
		log_action("<br>Disconnected Successfully...");
		exit();
	}
        
        $sql = "CREATE TABLE IF NOT EXISTS Classes (" .
                    "rowID int NOT NULL AUTO_INCREMENT, " .
                    "PRIMARY KEY (rowID), " .
                    "resourceID INT UNSIGNED, ".
                    "name VARCHAR(50))";
    
        if (mysql_query($sql,$link))
		log_action("connected successfully: Classes table");
	else {
		log_error("Error executing LastUpdated table check query: " . mysql_error());
		mysql_close($link);
		log_action("Disconnected Successfully...");                
		exit();
	}
        
        $sql = "CREATE TABLE IF NOT EXISTS LastUpdated (" .
                    "rowID int NOT NULL AUTO_INCREMENT, " .
                    "PRIMARY KEY (rowID), " .
                    "date DATETIME)";
    
        if (mysql_query($sql,$link))
		log_action("connected successfully: LastUpdated table");
	else {
		log_error("Error executing LastUpdated table check query: " . mysql_error());
		mysql_close($link);
		log_action("Disconnected Successfully...");                
		exit();
	}
}
}

function updatedbclass($array, $link){
    if ($array)
    if (!$link)
	exit("Could not connect" . mysql_error());
    else {
	log_action('connected successfully: mysql');
        
        mysql_select_db("reorder_db", $link);
        
        $updatequery = "UPDATE Classes SET name='".$array->name."' ".
                        "WHERE resourceID='".$array->attributes()->id."'; ".
                        "SELECT row_count();";
            
	if ($re = mysql_query($updatequery))
            while ($result[] = mysql_fetch_array($re));
            
        if ($result[0][0]==1)
            log_action('Record updated successfully: Class - '.$array->name);

        else {
            
            $insertquery =  "INSERT INTO Classes (resourceID, name) ".
                            "VALUES (".$array->attributes()->id.",".
                                     "'".$array->name."')";
                            
            if (mysql_query($insertquery))
                log_action('Record added successfully: Class - '.$array->name);
            else
                log_error("Error inserting class: " . mysql_error());
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
	exit("Could not connect" . mysql_error());
    else {
	log_action('connected successfully: mysql');
        
        mysql_select_db("reorder_db", $link);
        
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
            
	if (mysql_query($updatequery))
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
                            
            if (mysql_query($insertquery))
                log_action('Record added successfully: Product Code - '.$array->code);
            else
                log_error("Error inserting record: " . mysql_error());
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
	exit("Could not connect" . mysql_error());
    else {
    
        mysql_select_db("reorder_db", $link);
        
        $datequery =    "INSERT INTO LastUpdated (date)".
                        "VALUES ('".date('Y-m-d H:i:s')."')";
        
        if (mysql_query($datequery))
            log_action("Record added successfully: Date - ".date('Y-m-d H:i:s'));
        else
            log_error("Error inserting date: " . mysql_error());
    }
}


function getreorderproducts($link, $desc, $class) {
    if (!$link)
		exit("Could not connect" . mysql_error());
    else {
	//echo "connected successfully: mysql<br>";
        
        mysql_select_db("reorder_db", $link);
           
        
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
            $res = mysql_query($tq);
            if ($res)
                while ($res1[] = mysql_fetch_array($res));
            else
                log_error("Error running SELECT query: ". mysql_error());
            
            //print_r($res1);
            //echo '<br><br>';
            
            $q2 = " AND (classID='".$res1[0][0]."'";
            
            $x=1;
            while ($res1[$x][0])
                $q2 .= " OR classID='".$res1[$x++][0]."'";
            
            $q2 .= ")";
                
        }
        else 
            $q2 = null;
        
        $query = "SELECT * FROM Reorders WHERE ropoint >= available".$q1.$q2.";";
        //echo $query."<br><br>";
        
        $re = mysql_query($query);
        if ($re)
            while ($result[] = mysql_fetch_array($re)) ;
        else
            log_error("Error running SELECT query: ". mysql_error());
            //echo "Error running SELECT query: ". mysql_error (). "<br><br>";
        
        return $result;
    }
}

function getdatelastupdatedb($link){

    if (!$link)
		exit("Could not connect" . mysql_error());
    else {
		//echo "connected successfully: mysql<br>";

        mysql_select_db("reorder_db", $link);
        
        $query = "SELECT date FROM LastUpdated ORDER BY rowID DESC LIMIT 1;";
        
        $re = mysql_query($query);
        if ($re) {
            while ($result[] = mysql_fetch_array($re)) ;
            return $result[0]['date'];
        }
        else {
            log_error("Error getting date: ". mysql_error ());
            //echo "Error getting date: ". mysql_error (). "<br><br>";
            return 'n/a';
        }

     //mysql_close($link);
     
    }
}

?>
