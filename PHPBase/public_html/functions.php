<?php
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';

function show_errors($action){

    $error = false;

    if(!empty($action['result'])){
        
        if(is_array($action['text'])){
            foreach($action['text'] as $text){
                $error .= "<strong>ERROR!</strong> $text"."<br/>";
             }	
         }
         else{
             $error .= "<strong>ERROR!</strong> $action[text]";
         }
         
         $error .= "<br/>";
    }

    return $error;

}

function show_messages($action){

    $error = false;

    if(!empty($action['result'])){
        
        if(is_array($action['text'])){
            foreach($action['text'] as $text){
                $error .= "<strong>INFO!</strong> $text"."<br/>";
             }	
         }
         else{
             $error .= "<strong>INFO!</strong> $action[text]";
         }
         
         $error .= "<br/>";
    }

    return $error;

}

function show_successMessages($action){

    $error = false;

    if(!empty($action['result'])){
        
        if(is_array($action['text'])){
            foreach($action['text'] as $text){
                $error .= "<strong>SUCCESS!</strong> $text"."<br/>";
             }	
         }
         else{
             $error .= "<strong>SUCCESS!</strong> $action[text]";
         }
         
         $error .= "<br/>";
    }

    return $error;

}


?>
