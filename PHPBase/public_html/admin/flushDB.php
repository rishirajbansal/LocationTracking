<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once '../functions.php';
include_once 'sessionMgmt.php';

$action = array();
$action['result'] = null;
$text = array();
$message = NULL;

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

$locationHistoryDAO = new LocationHistoryDAO();

if (isset($_POST['submit'])){
    
    $flag = $locationHistoryDAO->flushDB();
        
    if (!$flag){
        $action['result'] = 'error'; 
        array_push($text,$locationHistoryDAO->getError());
        $action['text'] = $text;
    }
    else{
        $action['result'] = 'message'; 
        array_push($text, 'Database is flushed successfully.');
        $action['text'] = $text;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Location History &middot; Flush Database</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="../css/bootstrap.css" rel="stylesheet">
        <link href="../css/bootstrap-responsive.css" rel="stylesheet">
        <link href="../css/docs.css" rel="stylesheet">
        <link href="../js/google-code-prettify/prettify.css" rel="stylesheet">
        
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
          <script src="../js/html5shiv.js"></script>
        <![endif]-->
        
    </head>

    <body>
        
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
        
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Flush Database Records</h1>
              <p class="lead" style="font-size:17px">Delete all records from the database and clear it</p>
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
            
            <form class="form" id="flushDBForm" name="flushDBForm" method="post" action="">
                <br/><br/>
                <div class="page-header" style="text-align: center;">
                    <h1>Flush Database</h1>
                </div>

                <div class="jumboHeading">
                    <p class="lead" ><h3 style="color: #FF0000">Are you sure want to flush out the database records ?</h3></p>
                    <p style="font-size: 16px" >Be cautious, this process cannot be undone. This is the final confirmation.</p>
                    <br/><br/><br/><br/>
                    <button name="submit" value="submit" type="submit" class="btn btn-danger" style="margin-left: 40px">Flush !</button>
                    <a class="btn btn-large btn" href="home.php" style="margin-left: 40px">Cancel</a>
                </div>
            </form>
        </div>
       

        <!-- Include bottom bar page -->
        <?php include("bottombar.php"); ?>


        <script src="../js/jquery.js"></script>
        <script src="../js/bootstrap-transition.js"></script>
        <script src="../js/bootstrap-alert.js"></script>
        <script src="../js/bootstrap-modal.js"></script>
        <script src="../js/bootstrap-dropdown.js"></script>
        <script src="../js/bootstrap-scrollspy.js"></script>
        <script src="../js/bootstrap-tab.js"></script>
        <script src="../js/bootstrap-tooltip.js"></script>
        <script src="../js/bootstrap-popover.js"></script>
        <script src="../js/bootstrap-button.js"></script>
        <script src="../js/bootstrap-collapse.js"></script>
        <script src="../js/bootstrap-carousel.js"></script>
        <script src="../js/bootstrap-typeahead.js"></script>
        <script src="../js/bootstrap-affix.js"></script>

        <script src="../js/holder/holder.js"></script>
        <script src="../js/google-code-prettify/prettify.js"></script>

        <script src="../js/application.js"></script>

    </body>
</html>