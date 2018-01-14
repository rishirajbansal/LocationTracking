<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'JobReporting.php';
include_once '../functions.php';


$action = array();
$action['result'] = null;
$text = array();
$message = NULL;

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

$jobReporting = new JobReporting();
$flag = $jobReporting->fetchGoogleFormBody();
$formBody = $jobReporting->getGoogleFormBody();

$redirectURL = '';

if (session_id() == "") 
    session_start();

if ($flag){
   
    $redirectURL = "http://" . $_SERVER['HTTP_HOST'] ."/". 'LocationTracking'."/".'public_html'."/".'reporting'."/"."step2.php";
    //echo $redirectURL;
    
    $_SESSION['reporting'] = time();
}
else{
    $action['result'] = 'error'; 
    array_push($text,$jobReporting->getError());
    $action['text'] = $text;
}

if (isset($_POST['submit'])){
    
    //preventSQLInjectionAndValidate();
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Job Reporting</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="../css/bootstrap.css" rel="stylesheet">
        <link href="../css/bootstrap-responsive.css" rel="stylesheet">
        <link href="../css/docs.css" rel="stylesheet">
        <link href="../js/google-code-prettify/prettify.css" rel="stylesheet">
        
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:400,700">
        <link href='../css/googleform.css' type='text/css' rel='stylesheet'>
        <link href='../css/googleform_mobile.css' type='text/css' rel='stylesheet' media='screen and (max-device-width: 721px)'>
        
        <style type="text/css">
            body {
              padding-top: 40px;
              padding-bottom: 40px;
              background-color: #f5f5f5;
            }

            .form-jobreport {
              max-width: 750px;
              padding: 19px 29px 29px;
              margin: 0 auto 20px;
              background-color: #fff;
              border: 1px solid #e5e5e5;
              -webkit-border-radius: 5px;
                 -moz-border-radius: 5px;
                      border-radius: 5px;
              -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                 -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                      box-shadow: 0 1px 2px rgba(0,0,0,.05);
            }
            

          </style>
          
          <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
            <!--[if lt IE 9]>
              <script src="../assets/js/html5shiv.js"></script>
            <![endif]-->
          
          <script type="text/javascript">var submitted=false;</script>

    </head>

    <body> 
        
        <?php
            if (!empty($action['result']) && $action['result'] == 'error'){  ?>
                <div class="alert alert-error" style="margin-bottom: 0px;padding-bottom: 0px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo show_errors($action); ?>
                </div>
                <br/><br/>
        <?php  }
        ?>
        
        <?php
            if (!empty($action['result']) && $action['result'] == 'message'){  ?>
                <div class="alert alert-info" style="margin-bottom: 0px;padding-bottom: 0px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo show_messages($action); ?>
                </div>
                <br/><br/>
          <?php  }
          ?>                
       
       <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted){window.location=' <?php echo $redirectURL; ?>';}"></iframe>         
                
        <div class="container">
            
            <?php echo $formBody; ?>
            
        </div>
        
       <!-- Include bottom bar page -->
        <?php include("../bottombar.php"); ?>
       
    </body>
</html>