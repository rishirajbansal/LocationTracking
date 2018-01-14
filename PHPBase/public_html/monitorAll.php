<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Project.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistory.php';
include_once 'functions.php';
include_once 'sessionMgmt.php';

$action = array();
$action['result'] = null;
$text = array();
$message = NULL;

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

$locationHistoryDAO = new LocationHistoryDAO();

$inputs = new Inputs();
$locationHistoryProjectAndWorkers = NULL;
$projects = NULL;

$flag = $locationHistoryDAO->fetchLocationHistoryForAllWorkersProjects($user);
        
if (!$flag){
    $var = $locationHistoryDAO->getError();
    if (!empty($var)){
        $action['result'] = 'error'; 
        array_push($text,$locationHistoryDAO->getError());
        $action['text'] = $text;
    }
    else{
        $action['result'] = 'message'; 
        array_push($text,$locationHistoryDAO->getMessage());
        $action['text'] = $text;
    }
}
else{
    $action['result'] = 'success';

    $locationHistoryProjectAndWorkers = $locationHistoryDAO->getLocationHistoryProjectAndWorkers();
    $projects = $locationHistoryDAO->getProjects();
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

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
        
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-responsive.css" rel="stylesheet">
        <link href="css/docs.css" rel="stylesheet">
        <link href="js/google-code-prettify/prettify.css" rel="stylesheet">
        
        <style type="text/css">
            @media (max-width: 980px) {
              /* Enable use of floated navbar text */
              .navbar-text.pull-right {
                float: none;
                padding-left: 5px;
                padding-right: 5px;
              }
            }
            .table tbody tr.warning > td {
                background-color: #FFFFFF !important;
            }
            .table tbody tr.plain > td {
                background-color: #f9f9f9;
            }
            .close {
                font-size: 30px;
                color: #FF0000;
                opacity: .5;
             }
             .hero-unit {
                 line-height: normal;
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
              <h1 style="font-size:27px">Monitor Workers and Project Locations</h1>
              <p class="lead" style="font-size:17px">Monitor current location of workers and the project locations</p>
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
        
        <div class="container-fluid" style="padding-top: 30px;  ">
            <div class="row-fluid">
                
                <form class="form-inline" id="monitorworkersForm" name="monitorworkersForm" method="post" action="">
                    <input type="hidden" name="workersSelected" id="workersSelected" value="" />
                    <input type="hidden" name="projectSelected" id="projectSelected" value="" />
                    
                    <div>
                        <div class="hero-unit" style="background-color: #FFFFFF; min-height: 400px; padding: 20px 0px">
                            
                            <?php
                    
                            if ($action['result'] == 'success') { ?>
                            
                                <div style="display: inline-block"><h4 style="margin: 0px">Current Status</h4></div>
                            
                                <div style="display: inline-block;float: right">
                                    <a href="#details" class="btn btn-link">Project Details</a>
                                    <a href="#details" class="btn btn-link">Workers Details</a>
                                </div>
                                
                                <hr style="margin: 10px 0;">
                                <br/>
                            
                                <div class="span12" style="  margin-top: 10px;text-align: center;margin-left: 0px;" >
                                    <?php
                                          $filename = Config::$xmlFile . '-' . 'MonitorWorkersProjects' . '.xml';
                                    ?>
                                    <iframe align="center" style="  height: 900px;width: 1500px;" src="mapMonitorWorkersProjects.php?filename=<?php echo $filename; ?>"  frameborder="yes" scrolling="yes"> </iframe>
                                </div>
                            
                                <div class="span12" style="  margin-top: 35px;margin-left:0px" >
                                    <div class="span6" style="margin-left: 5px">
                                        <h5>Project Details : </h5>
                                        <table class="table table-bordered table-striped" style="font-size: 14px;">
                                            <thead>
                                                <tr class="label-info" style="color: #FFFFFF">
                                                    <th width="25%">Name</th>
                                                    <th width="22%">Coordinates</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    foreach ($projects as $project){ ?>
                                                        <tr>
                                                            <td><?php echo $project->getProjectname();?></td>
                                                            <td><?php echo $project->getLatitude() . ' / ' . $project->getLongitude();?></td>
                                                            <td><?php echo $project->getLocation();?></td>
                                                        </tr>
                                                     <?php }
                                                ?>

                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="span6" style="margin-left: 15px" name="details">
                                        <a name="details"></a>
                                        <h5>Worker(s) Details : </h5>
                                        <table class="table table-bordered table-striped" style="font-size: 14px;" >
                                            <thead>
                                                <tr class="label-info" style="color: #FFFFFF">
                                                    <th style="text-align: center;" width="5%">#</th>
                                                    <th width="17%">Worker</th>
                                                    <th width="12%">Timestamp <font style="font-size: 10px">(Last Recorded)</font></th>
                                                    <th width="24%">Coordinates</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $ctr = 1;
                                                    $rowToggle=0;
                                                    foreach ($locationHistoryProjectAndWorkers as $record) { 
                                                            $workerName = $record['workername'];
                                                            $locationhistory = $record['locationhistory'];
                                                            ?>
                                                            <tr <?php if ($rowToggle) { $rowToggle=0?>class="warning" <?php } else { ?> class="" <?php $rowToggle=1; } ?>>
                                                                <td style="text-align: center;"><?php echo $ctr;?></td>
                                                                <td><?php echo $workerName;?></td>
                                                                <?php if (isset($locationhistory) && $locationhistory->getTime() != 'none') { ?>
                                                                    <td><?php echo $locationhistory->getTime();?></td>
                                                                    <td><?php echo $locationhistory->getLatitude() . ' / ' . $locationhistory->getLongitude();?></td>
                                                                    <td><?php echo $locationhistory->getLocation();?></td>
                                                                <?php }
                                                                else { ?>
                                                                    <td colspan="3">No Records</td>
                                                                <?php }
                                                               ?>
                                                            </tr>
                                                <?php 
                                                    $ctr+=1 ; 
                                                     }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            <?php } ?>
                                
                        </div>
                    </div>
                </form>
                
            </div>
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

