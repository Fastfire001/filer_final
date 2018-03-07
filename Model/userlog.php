<?php
function userTracker($action){
    if (!empty($_SESSION['username'])){
        $begin = 'User '.$_SESSION['username'] . '(' . $_SESSION['id'] . ')';
    }else{
        $begin = 'Unknown user';
    }
    return $begin.' '.$_GET['action'].' at '.date('r') . ':' ;
}
function writeToLog($newMessage, $security = true){
    if ($security === false){
        $file = fopen('securitylogs/access.log', 'ab');
    }else{
        $file = fopen('securitylogs/security.log', 'ab');
    }
   // $newMessage = $this->userTracker().$newMessage;
    fwrite($file, $newMessage."\n");
    fclose($file);
}