<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once '../functions.php';
include_once 'sessionMgmt.php';

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Location History &middot; Admin Panel</title>
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
        
        <div class="container page">
            
            <div class="jumboHeading">
                <img src="../img/admin.ico" class="img-rounded" width="10%">
                <h1><img src="../img/settings.ico" class="img-rounded" width="5%">&nbsp;&nbsp;Administration Panel&nbsp;&nbsp;<img src="../img/settings.ico" class="img-rounded" width="5%"></h1>
                <p class="lead">Welcome ! Here is the brief snapshot about the Admin Panel's functionalities.</p>
                <br/>
                
                <div style="text-align: left;margin-left:150px">
                    <dl>
                        <!--<dt><h4>Monitor</h4></dt>
                        <dd>Monitor current location of workers and the project locations. All workers and projects will appear on same map</dd>
                        <br/>-->
                        <dt><h4>Domain Management</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Add New Domain</strong> - Create Domain to distinguish application data and group them for the intended tenants.</li>
                                <li><strong>List Domains</strong> - List of the existing domain with details.</li>
                            </ul>
                        </dd>
                        <br/>
                        <dt><h4>Users Management</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Add New User</strong> - Add new User to allow 'view' access of the application.</li>
                                <li><strong>List Users</strong> - List of the existing users with details.</li>
                            </ul>
                        </dd>
                        <br/>
                        <dt><h4>Project Locations Management</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Add New Project Location</strong> - Create Personalized points on the map to depict the project location.</li>
                                <li><strong>List Project Locations</strong> - List of the existing projects with details.</li>
                            </ul>
                        </dd>
                        <br/>
                        <dt><h4>Workers Management</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Add New Worker</strong> - Add new Worker that will be monitored by the application.</li>
                                <li><strong>List Workers</strong> - List of the existing workers with details.</li>
                            </ul>
                        </dd>
                        <br/>
                        <dt><h4>Settings</h4></dt>
                        <dd>
                            <ul>
                                <li><strong>Change Super User Password</strong> - Replace Super User Password with new password.</li>
                                <!-- <li><strong>Flush Database</strong> - Reset the database to blank by flushing out all the records from the database.</li> -->
                            </ul>
                        </dd>
                    </dl>
                </div>
                
            </div>
            <hr>
            
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