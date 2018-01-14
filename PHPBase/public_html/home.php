<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once 'functions.php';
include_once 'sessionMgmt.php';

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

$locationHistoryDAO = new LocationHistoryDAO();
$flag = $locationHistoryDAO->fetchAllWorkers($user);

if ($flag){
}
else{
    $action['result'] = 'error'; 
    array_push($text,$locationHistoryDAO->getError());
    $action['text'] = $text;
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Location History &middot; Time Based Summary</title>
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
          <script src="../js/html5shiv.js"></script>
        <![endif]-->
        
    </head>

    <body>
        
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
        
        <div class="container page">
            
            <div class="jumboHeading">
                <img src="img/historyimg.png" class="img-rounded" width="25%">
                <h1><img src="img/pushpin.png" class="img-rounded" width="5%">&nbsp;&nbsp;Tracking Location History&nbsp;&nbsp;<img src="img/pushpin.png" class="img-rounded" width="5%"></h1>
                <p class="lead">Welcome ! Here is the brief snapshot about the application functionalities.</p>
                <br/>
                
                <div style="text-align: left;margin-left:150px">
                    <dl>
                        <!--<dt><h4>Monetarization</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Monitor All</strong> - Monitor current location of workers and the project locations. All workers and projects will appear on same map.</li>
                                <li><strong>Project Based Monitoring</strong> - Monitor current location of workers within the certain radius based on the project location. </li>
                            </ul>
                        </dd>-->
                        <dt><h4>Monitor</h4></dt>
                        <dd>Monitor current location of workers and the project locations. All workers and projects will appear on same map</dd>
                        <br/>
                        <dt><h4>Reports</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Time Based Summary</strong> - Report to get the location history based on the selected time interval.</li>
                                <!--<li><strong>Day Based Summary</strong> - Report to get ALL the history of all the locations based on the selected day.</li>-->
                                <li><strong>Distance Based Summary</strong> - Measure the distances in kms between periods of time of workers.</li>
                                <li><strong>Project Location Based Summary</strong> - Measures the total time spent by the worker in the selected project location within the selected proximity.</li>
                                <li><strong>Stops Based Summary</strong> - Report to filter out the records of stop positions or the worker based on the selected time interval.</li>
                            </ul>
                        </dd>
                        <br/>
                        <dt><h4>Application Configuration</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Setup Google Account</strong> - Set Google Drive account where the report responses will be saved.</li>
                                <li><strong>Setup Google Drive Folder</strong> - Set Google Drive Folder to save the generated responses.</li>
                            </ul>
                        </dd>
                         <br/>
                        <dt><h4>My Account</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Change Password</strong> - Replace Your Password with new password.</li>
                            </ul>
                        </dd>
                    </dl>
                </div>
                
            </div>
            <hr>
            <?php
            if (!empty($action['result']) && $action['result'] == 'error'){  ?>
                <!--<div class="alert alert-error" style="margin-bottom: 50px;">
                    <strong>It seems that you have not added any worker in the system, please add worker to avoid seeing any internal errors in the system.
                    <br/>
                    You can add the workers from the backend tool which is a counterpart of the application.</strong>
                </div> -->
            <?php  }
            ?>
            
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