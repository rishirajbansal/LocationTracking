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

$responseArray = NULL;
$displayPage = FALSE;
$displaySuccess = FALSE;

if (session_id() == "") 
    session_start();

if (!empty($_SESSION['reporting'])){
    
    if (!isset($_POST['submit'])){
        //Save it to associate the current response with google sheet, fetch the response now
        $flag = $jobReporting->retrieveSubmittedResponseFromGoogle();
        $responseArray = $jobReporting->getResponseArray();
        $_SESSION['responseArray'] = $responseArray;

        if ($flag){
            $displayPage = TRUE;
        }
        else{
            $action['result'] = 'error'; 
            array_push($text,$jobReporting->getError());
            $action['text'] = $text;
        }
    }
    
}
else {
    $action['result'] = 'error'; 
    array_push($text,'Invalid Session. Unauthenticated.');
    $action['text'] = $text;
}

if (isset($_POST['submit']) && !empty($_SESSION['reporting'])){

    preventSQLInjectionAndValidate();
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        //Save signature
        $sig = filter_input(INPUT_POST, 'output', FILTER_UNSAFE_RAW);
        $flag = $jobReporting->save($sig, $_SESSION['responseArray']);
        
        if ($flag){
            $displayPage = FALSE;            
            $displaySuccess = TRUE;
            
            unset($_SESSION['reporting']);
            unset($_SESSION['responseArray']);
        }
        else{
            $action['result'] = 'error'; 
            array_push($text,$jobReporting->getError());
            $action['text'] = $text;
            
            $displayPage = TRUE;
        }
    }
    else{
        $displayPage = TRUE;
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
        
        <link href="../sign/jquery.signaturepad.css" rel="stylesheet">
        <!--[if lt IE 9]><script src=".../sign/flashcanvas.js"></script><![endif]-->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
        <script src="../sign/jquery.signaturepad.js"></script>
        <script src="../sign/json2.min.js"></script>
         <script>
             
            $(document).ready(function() {
                var options = {
                    drawOnly:true,
                    bgColour : '#F7F7F7',
                    penColour: '#E32929',
                    lineTop: '70',
                    errorMessageDraw: 'Signature is required. Please sign the document'
                  };
              $('.sigPad').signaturePad(options);
            });
            
            function offProgress(){
                <?php                     
                    if ($displayPage) { ?>
                        document.getElementById("progressDiv").style.display = "none";
                        document.body.style.cursor = 'default';
                <?php } ?>
            }
            
            function onProgress(){
                document.getElementById("progressDiv").style.display = "block";
                document.body.style.cursor = 'wait';
            }
            
          </script>
        
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
            .form-jobreport .form-jobreport-heading,
            .form-jobreport .checkbox {
              margin-bottom: 10px;
            }
            .form-jobreport input[type="text"],
            .form-jobreport input[type="password"] {
              font-size: 16px;
              height: auto;
              margin-bottom: 15px;
              padding: 7px 9px;
            }
            
            .sigPad {
                margin: 60px 0 20px 0;
                padding: 0;
                width: 402px;
              }

              .sigWrapper {
                clear: both;
                height: 80px;

                border: 2px solid #ccc;
              }

          </style>

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="../assets/js/html5shiv.js"></script>
        <![endif]-->
        
    </head>

    <body onload="javascript:offProgress();" onsubmit="javascript:onProgress();">
        
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
      
        <div class="container">
            
            <?php
                    
             if ($displayPage) { ?>
            
                <form class="form-jobreport" id="jobreportForm" name="jobreportForm" method="post" action="" enctype="multipart/form-data">

                    <h2 class="form-jobreport-heading">Job Report - Step 2</h2>
                    
                    <div id="progressDiv" style="display:none">
                        <br/>
                        <div class="alert " style="padding: 8px 35px 8px 35px;">
                            <br/>
                            <div class="progress progress-striped active">
                                <div class="bar" style="width: 100%;"></div>
                            </div>
                            <p class="lead"><h4 style="color: #FE0F0F;">Your report is being submitted...</h4></p> 
                            <p style="font-size: 16px;color: #000000" >This will take a moment. Please don't re-submit or refresh the page.</p>
                        </div>
                    </div>

                    <br/><br/>
                    <label for="photo" style="font-weight: bold;">Upload photo : </label>
                    <div style="border: 1px solid #ADB9BF; width: 400px; padding-left: 5px; border-radius: 4px;">
                        <input name="photo" type="file" id="photo" size="40" maxlength="60"  accept="image/*" required="true"  onfocus="javascript:offProgress();"/>
                    </div>

                    <div class="sigPad">
                        <label for="output" style="font-weight: bold;padding-top: 15px;" class="drawItDesc" >Client Signature : </label>


                        <div class="sig sigWrapper" style="border-color: #60A6C7;border-radius: 4px;">
                        <div class="typed"></div>
                            <canvas class="pad" width="398" height="80"></canvas>
                            <input type="hidden" name="output" class="output">
                        </div>
                        <ul class="sigNav">
                            <li class="clearButton"><a href="#clear">Clear</a></li>
                        </ul>
                    </div>

                    <br/><br/>
                    <button id="submit" name="submit" value="submit" type="submit" class="btn btn-primary">Submit Report</button>

                </form>
            
            <?php }
            else if ($displaySuccess) { ?>
                <div class="form-jobreport">
                    <h2 class="form-jobreport-heading">Job Report</h2>
                    
                    <br/>
                    <div class="alert alert-info" style="padding: 8px 35px 8px 35px;">
                        <br/>
                        <p class="lead"><h3 style="color: #1886AA;text-shadow: 2px 2px #FFFFFF;"">Thank You !</h3></p> 
                        <p style="font-size: 18px;color: #000000" >Your response has been submitted.</p>
                         <br/>
                        <p style="font-size: 14px;color: #000000" >You can close this window now.</p>
                        <br/>
                    </div>
                </div>
            
            <?php } ?>
                
       </div> <!-- /container -->
       
        <!-- Include bottom bar page -->
        <?php include("../bottombar.php"); ?>


    </body>
</html>

<?php

function preventSQLInjectionAndValidate(){
    global $action, $text;
    
    if( $_FILES['photo']['name'] == "" ) { $action['result'] = 'error'; array_push($text,'It seems you forgot to upload the photo. Please upload photo.'); }
    
       
}

?>
