<?php
function userTracker($action){
    if (!empty($_SESSION['username'])){
        $begin = nl2br('User '.$_SESSION['username']) ;
    }else{
        $begin = 'Unknown user';
    }
    return $begin.' '.$action.' at '.date('r') .nl2br('\n');
}
function writeToLog($newMessage, $file){
    if ($file === 'access'){
        $file = fopen('securitylogs/access.log', 'ab');
    }else{
        $file = fopen('securitylogs/security.log', 'ab');
    }
    fwrite($file, $newMessage."\n");
    fclose($file);
}