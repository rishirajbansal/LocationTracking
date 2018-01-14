<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'JobReporting.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once 'functions.php';
include_once 'sessionMgmt.php';

$action = array();
$action['result'] = null;
$text = array();
$message = NULL;

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

$jobReporting = new JobReporting();

if (isset($_POST['submit'])){
    
    $flag = $jobReporting->gOAuthValidation();
        
    if (!$flag){
        $action['result'] = 'error'; 
        array_push($text,$jobReporting->getError());
        $action['text'] = $text;
    }
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    $flag = $jobReporting->setupGAccount($code);
    
    if (!$flag){
        $action['result'] = 'Googleerror'; 
        array_push($text, $jobReporting->getError());
        $action['text'] = $text;
    }
    else{
        $action['result'] = 'success';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Location History &middot; Setup Google Account</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-responsive.css" rel="stylesheet">
        <link href="css/docs.css" rel="stylesheet">
        <link href="js/google-code-prettify/prettify.css" rel="stylesheet">
        
        <style type="text/css">
            html, body {
                padding-top: 20px;
                padding-bottom: 60px;
                height: 100%;
              }

              /* Custom container */
              .container {
                margin: 0 auto;
                max-width: 1100px;
              }
              .container > hr {
                margin: 50px 0;
                border-top: 2px solid #31A5CD;
              }

             .jumboHeading {
                margin: 80px 0;
                margin-top: 50px;
                text-align: center;
              }
              .jumboHeading h1 {
                font-size: 70px;
                line-height: 1;
              }
              .jumboHeading .lead {
                font-size: 24px;
                line-height: 1.25;
              }
              .jumboHeading .btn {
                font-size: 21px;
                padding: 14px 24px;
              }  

            .page {
              min-height:100%;
              height: auto !important;
              height: 100%;
              /* Negative indent footer by it's height */
              margin: 0 auto -60px;
            }
            .close {
                font-size: 30px;
                color: #FF0000;
                opacity: .5;
             }
           
        </style>

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="../assets/js/html5shiv.js"></script>
        <![endif]-->
        
    </head>

    <body>
        
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
        
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Setup Google Account</h1>
              <p class="lead" style="font-size:17px">Set Google Drive account where the report responses will be saved</p>
           </div>
        </header>
        
        <?php
            if (!empty($action['result']) && $action['result'] == 'error'){  ?>
                <div class="alert alert-error" style="margin-bottom: 0px;padding-bottom: 0px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo show_errors($action); ?>
                </div>
        <?php  }
        ?>
        
        <?php
            if (!empty($action['result']) && $action['result'] == 'message'){  ?>
                <div class="alert alert-info" style="margin-bottom: 0px;padding-bottom: 0px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo show_messages($action); ?>
                </div>
          <?php  }
          ?>
        
        <div class="container page">
            
                <form class="form" id="setGAccountForm" name="setGAccountForm" method="post" action="">
                    <br/><br/>
                    <div class="page-header" style="text-align: center;">
                        <h1>Setup Google Account</h1>
                    </div>

                    <div class="jumboHeading">
                        
                        <?php
                            if (!empty($action['result']) && $action['result'] == 'success'){  ?>
                                <div class="alert alert-info" style="margin-left: 10%;margin-right: 10%;">

                                    <p class="lead"><h3 style="color: #1886AA;text-shadow: 2px 2px #FFFFFF;">Google Account has been setup successfully.
                                        <br/><br/>Credentials and tokens are stored.</h3></p>         
                                   
                                  <br/><br/>
                                   <p style="font-size: 18px;color: #FF0000;text-shadow: 2px 2px #FFFFFF;" >Please remember to update the 'Google Drive Folder' URL configuration. 
                                       To update it now, <strong><u><a href="setGDriveFolder.php" style="font-size: 18px;color: #FF0000;text-shadow: 2px 2px #FFFFFF;" title="Click to update Drive Folder">click here </a></u></strong></p>
                                   <br/>
                                </div>
                                <br/><br/>
                                <a class="btn btn-success btn" href="home.php" style="margin-left: 40px">Back to Home</a>
                        <?php  } 
                        
                       else if (!empty($action['result']) && $action['result'] == 'Googleerror'){  ?>
                                <div class="alert alert-error" style="margin-left: 10%;margin-right: 10%;">

                                    <p class="lead"><h3 style="color: #FE0F0F;text-shadow: 2px 2px #FFFFFF;"><?php echo show_errors($action); ?></h3></p> 
                                    <p style="font-size: 18px;color: #000000" >If problem persists, contact administrator.</p>
                                    <br/>
                                </div>
                                <br/><br/>
                                <a class="btn btn-success btn" href="setGAccount.php" style="margin-left: 40px">Try Again</a>
                        <?php  } 

                        else { ?>
                        
                            <p class="lead" ><h3 style="color: #1886AA">On clicking 'Proceed', you will be redirected to Google Account login screen.
                                <br/>It will obtain consent for this application to access the data. 
                                <br/><br/>On consent screen, please chose 'Accept' to make the account setup successful.</h3></p>
                                <br/>
                            <p style="font-size: 18px;color: #FF0000" >If any previous account is setup, that will be removed.</p>
                            <br/><br/>
                            <p style="font-size: 16px;font-style: italic;color: #000000" >If during this process any failures occurs or process failed, the current account will keep working, it will not be removed.</p>
                            <br/><br/>
                            <strong><p style="font-size: 18px;color: #000000" >If this is being setup for previously used account, then please ensure that the application do not exist in 'Manage Apps' else it will not function perfectly after setup.</p></strong>
                            <br/><br/>
                            <img src="img/ExistingApp.png" class="img-rounded" >
                            
                            <br/><br/><br/><br/>
                            <button name="submit" value="submit" type="submit" class="btn btn-danger" style="margin-left: 40px">Proceed</button>
                            <a class="btn btn-large btn" href="home.php" style="margin-left: 40px">Cancel</a>
                        
                         <?php } ?>
                    </div>
                    
                </form>
            
        </div>
       

        <!-- Include bottom bar page -->
        <?php include("bottombar.php"); ?>


        <script src="js/jquery.js"></script>
        <script src="js/bootstrap-transition.js"></script>
        <script src="js/bootstrap-alert.js"></script>
        <script src="js/bootstrap-modal.js"></script>
        <script src="js/bootstrap-dropdown.js"></script>
        <script src="js/bootstrap-scrollspy.js"></script>
        <script src="js/bootstrap-tab.js"></script>
        <script src="js/bootstrap-tooltip.js"></script>
        <script src="js/bootstrap-popover.js"></script>
        <script src="js/bootstrap-button.js"></script>
        <script src="js/bootstrap-collapse.js"></script>
        <script src="js/bootstrap-carousel.js"></script>
        <script src="js/bootstrap-typeahead.js"></script>
        <script src="js/bootstrap-affix.js"></script>

        <script src="js/holder/holder.js"></script>
        <script src="js/google-code-prettify/prettify.js"></script>

        <script src="js/application.js"></script>

    </body>
</html>