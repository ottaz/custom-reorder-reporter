<?php

/*
 * errorlogs.php
 *
 * Place actions and errors into monthly log file.
 *
 */

function log_action($string){

$textfile = 'logs/logfile-'.date('MY').'.log';
$tab = "\t"; $newline = "\r";

if (!$handle = fopen($textfile, 'a')) 
        die('Cannot open file: '.$textfile);

if (fwrite($handle, "[".date('j M Y h:i:s a')."]".utf8_encode($tab).
                        $string.utf8_encode($newline)
            ) === false)
       die('Cannot write to file: '.$textfile);

fclose($handle);

}

function log_error($string){
    
$textfile = 'logs/logfile-'.date('MY').'.log';
$tab = "\t"; $newline = "\r";

if (!$handle = fopen($textfile, 'a')) 
        die('Cannot open file: '.$textfile);

if (fwrite($handle, "[".date('j M Y h:i:s a')."]".utf8_encode($tab).
                        $string.utf8_encode($newline)
            ) === false)
       die('Cannot write to file: '.$textfile);

fclose($handle);

}

?>