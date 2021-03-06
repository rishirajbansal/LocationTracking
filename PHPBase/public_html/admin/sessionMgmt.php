<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';


if (session_id() == "") 
    session_start();

if (empty($_SESSION['loggedIn']) || ($_SESSION['loggedIn'] != 1) || empty($_SESSION['isAdmin']) || ($_SESSION['isAdmin'] != 1)) {    
    header("location: login.php");
}

if (!validateSession($_SESSION['begin'])){
    header("location: logout.php?sessionTimeout=1");
}
else{
    $_SESSION['begin'] = time();
}


function validateSession($sessionTime){
    
    $secondsInactive = time() - $sessionTime;
    
    $sessionExpiry = Config::$sessionTimeout * 60;
    
    if ($secondsInactive >= $sessionExpiry){
        return false;
    }
    else{
        return true;
    }
    
}

?>
