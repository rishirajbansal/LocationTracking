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

$flag1 = $locationHistoryDAO->fetchAllWorkers($user);
$workers = $locationHistoryDAO->getWorkers();

$flag2 = $locationHistoryDAO->fetchProjects($user);
$projects = $locationHistoryDAO->getProjects();

$locationHistoryProjectBased = NULL;
$project = NULL;

$inputs = new Inputs();

if (!$flag1 || !$flag2){
    $action['result'] = 'error'; 
    array_push($text,$locationHistoryDAO->getError());
    $action['text'] = $text;
}
    
if (isset($_POST['submit'])){
    
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        //Retrieve location history records based on the worker(s) date
        $flag = $locationHistoryDAO->fetchLocationHistoryBasedOnProject($inputs);
        
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
            
            $locationHistoryProjectBased = $locationHistoryDAO->getLocationHistoryProjectBased();
            $project = $locationHistoryDAO->getProject();
        }
        
    }
        
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
        
        <script type="text/javascript">
        
            function readWorkerValues(){
                var ctr = 0;
                var value = "";
                while(document.getElementById("worker").length > ctr){
                    var optionSelected = document.getElementById("worker").options[ctr].selected;
                    if (optionSelected){
                        value = value + document.getElementById("worker").options[ctr].value + ",";
                    }
                    ctr = ctr + 1;
                }
                if (value != ""){
                    value = value.substr(0, value.length - 1);
                }
                document.getElementById("workersSelected").value = value;
                
                document.getElementById("projectSelected").value = document.getElementById("project").options[document.getElementById("project").selectedIndex].text;

            }
            
        </script>
        
    </head>

    <body>
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Monitor Workers Location - Project Wise</h1>
              <p class="lead" style="font-size:17px">Monitor current location of worker(s) within the certain radius of the project</p>
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
                    
                    <div class="well sidebar-nav" >
                        <h4 style="margin: 0px">Search Filter</h4>

                        <hr style="margin: 2px 0;">
                        <br/>
                        
                        <div style="margin-bottom: 10px;"> 
                            <label for="project" style="font-weight: bold;">Select Project : </label>
                            <select name="project" id="project" required>
                                <option value="0" <?php if (!isset($inputs)){ ?> selected="selected" <?php } ?> >*** Select Project ***</option>
                                <?php 
                                foreach ($projects as $project1){?>
                                
                                <option value="<?php echo $project1->getIdproject(); ?>" <?php if (isset($inputs) && ($project1->getIdproject() == $inputs->getProjectid())){ ?> selected="selected" <?php } ?> ><?php echo $project1->getProjectname(); ?></option>
                               <?php }
                                ?>
                            </select>
                            <label for="worker" style="padding-left: 30px;  font-weight: bold;">Select Worker : </label>
                            <select name="worker" id="worker" required multiple="multiple" size="3" style="min-width: 300px;" >
                                <?php 
                                $temp = '';
                                if (isset($inputs)){ $temp = $inputs->getWorkeridList(); } ?>
                                <option value="-1" <?php if (isset($inputs) && !empty($temp) && (in_array(-1, $inputs->getWorkeridList())) ){ ?> selected="selected" <?php } ?> style="color: #1497E0;">ALL</option>
                                <?php 
                                foreach ($workers as $worker){?>
                                <option value="<?php echo $worker->getIdworker(); ?>" <?php if (isset($inputs) && !empty($temp) && (in_array($worker->getIdworker(), $inputs->getWorkeridList())) ){ ?> selected="selected" <?php } ?> ><?php echo $worker->getName(); ?></option>
                               <?php }
                                ?>
                             </select>
                            
                            <label for="radius" style="padding-left: 30px;  font-weight: bold;">Radius : </label>
                            <input name="radius" id="radius" type="number" value="<?php if (isset($inputs) && ($inputs->getRadius() != '') ) { echo $inputs->getRadius(); } else { echo '5'; ?><?php } ?>" min="1" required style="max-width: 100px;"/>

                            <label for="units" style="padding-left: 30px;  font-weight: bold;">Units : </label>
                            <select name="units" id="units" required>
                                <option value="kms" <?php if (isset($inputs) && $inputs->getUnits() == "kms"){ ?> selected="selected" <?php } else if (!isset($inputs)) {?> selected="selected" <?php } ?> style="max-width: 150px;">In kilometers</option>
                                <option value="miles" <?php if (isset($inputs) && $inputs->getUnits() == "miles"){ ?> selected="selected" <?php } ?> >In Miles</option>
                            </select>

                            
                            <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left:  120px;" onclick="javascript:readWorkerValues();">Submit</button>
                            <button type="cancel" class="btn" style="margin-left: 10px">Cancel</button>
                        </div>
                    </div>
                    
                    <div>
                        <div class="hero-unit" style="background-color: #FFFFFF; min-height: 400px; padding: 20px 0px">
                            
                            <?php
                    
                            if ($action['result'] == 'success') { ?>
                            
                                <h4 style="margin: 0px">Search Results</h4>

                                <hr style="margin: 2px 0;">
                                <br/>
                                <div class="span12" style="margin-left: 0px">
                                    <h5>Project Details : </h5>
                                    <table class="table table-bordered table-striped" style="font-size: 14px;">
                                        <thead>
                                            <tr class="label-info" style="color: #FFFFFF">
                                                <th width="29%">Name</th>
                                                <th width="20%">Coordinates</th>
                                                <th>Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $project->getProjectname();?></td>
                                                <td><?php echo $project->getLatitude() . ' / ' . $project->getLongitude();?></td>
                                                <td><?php echo $project->getLocation();?></td>

                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="span6" style="margin-left: 0px">
                                    <h5>Worker(s) Details : </h5>
                                    <table class="table table-bordered table-striped" style="font-size: 14px;">
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
                                                foreach ($locationHistoryProjectBased as $record) { 
                                                        $workerName = $record['workername'];
                                                        $locationhistory = $record['locationhistory'];
                                                        ?>
                                                        <tr <?php if ($rowToggle) { $rowToggle=0?>class="warning" <?php } else { ?> class="" <?php $rowToggle=1; } ?>>
                                                            <td style="text-align: center;"><?php echo $ctr;?></td>
                                                            <td><?php echo $workerName;?></td>
                                                            <?php if (isset($locationhistory) && $locationhistory != 'none') { ?>
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

                                <div class="span6" style="  margin-top: 35px;" >
                                    <?php
                                          $filename = Config::$xmlFile . '-' . 'MonitorWorkers' . '.xml';
                                    ?>
                                    <iframe align="center" style="  height: 1000px;width: 820px;" src="mapMonitorLocationMarkers.php?filename=<?php echo $filename; ?>&centerLatitude=<?php echo $project->getLatitude(); ?>&centerLongitude=<?php echo $project->getLongitude(); ?>&radius=<?php echo $inputs->getRadiusInMeters(); ?>"  frameborder="yes" scrolling="yes"> </iframe>
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

<?php

function preventSQLInjectionAndValidate(Inputs $inputs){
    global $action, $text;
    
    $projectid = mysql_real_escape_string($_POST['project']);
    $projectname = mysql_real_escape_string($_POST['projectSelected']);
    $workersSelected = mysql_real_escape_string($_POST['workersSelected']);
    $radius = mysql_real_escape_string($_POST['radius']);
    $units = mysql_real_escape_string($_POST['units']);
    
    if (empty($projectid) || $projectid == "0"){ $action['result'] = 'error'; array_push($text,'Please select Project'); }
    if (empty($radius)){ $action['result'] = 'error'; array_push($text,'Please enter Radius'); }
    if (empty($units)){ $action['result'] = 'error'; array_push($text,'Please select Units'); }
    
    if (empty($workersSelected)){ 
        $action['result'] = 'error'; array_push($text,'Please select Worker');
    }
    else{
        $inputs->setWorkeridList(explode(",", $workersSelected));
    }
    
    $inputs->setProjectid($projectid);
    $inputs->setProjectname($projectname);
    $inputs->setRadius($radius);
    $inputs->setUnits($units);
}

?>
