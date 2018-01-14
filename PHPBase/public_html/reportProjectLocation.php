<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Project.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistory.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'ProjectLocationSummary.php';

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

$projectLocationSummary = new ProjectLocationSummary();
$project = NULL;

$inputs = new Inputs();

if (!$flag1 || !$flag2){
    $action['result'] = 'error'; 
    array_push($text,$locationHistoryDAO->getError());
    $action['text'] = $text;
}
    
if (isset($_POST['search'])){
    
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        //Retrieve location history records based on the distances
        $flag = $locationHistoryDAO->fetchLocationHistoryForProjectLocationReport($inputs, $user);
        
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
            
            $projectLocationSummary = $locationHistoryDAO->getProjectLocationSummary();
            $project = $locationHistoryDAO->getProject();
            
            $_SESSION['reportData1'] = $projectLocationSummary;
            $_SESSION['reportData2'] = $project;
            $_SESSION['reportInputs'] = $inputs;
        }
        
    }
        
}
else if (isset($_POST['export']) && $_POST['export'] == "true"){
    
    header('location: downloadReport.php?report=projectLocationSummary&type='.$_POST['exportType']);
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Location History &middot; Project Location Based Summary</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
        
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-responsive.css" rel="stylesheet">
        <link href="css/docs.css" rel="stylesheet">
        <link href="js/google-code-prettify/prettify.css" rel="stylesheet">
        <link href="css/fileicon.css" rel="stylesheet">
        
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
             html, body {
                padding-top: 20px;
                height: 90%;
              }
              .page {
              min-height:100%;
              height: auto !important;
              height: 100%;
              /* Negative indent footer by it's height */
              margin: 0 auto -60px;
            }
             
            .table tbody tr td {
                padding-bottom: 20px;
                padding-top: 20px;
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
                        value = document.getElementById("worker").options[ctr].text;
                        break;
                    }
                    ctr = ctr + 1;
                }
                
                document.getElementById("workerSelected").value = value;
                
                document.getElementById("projectSelected").value = document.getElementById("project").options[document.getElementById("project").selectedIndex].text;

            }
            
            function handleExports(type){
                document.getElementById("export").value = "true";
                document.getElementById("exportType").value = type;
                
                document.forms['reportProjectLocationForm'].submit();
            }
            
        </script>
        
    </head>

    <body>
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Project Location Based Summary</h1>
              <p class="lead" style="font-size:17px">Measures the total time spent by the worker in the selected project location within the selected proximity</p>
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
        
        <div class="container-fluid page" style="padding-top: 30px;  ">
            <div class="row-fluid">
                
                <form class="form-inline" id="reportProjectLocationForm" name="reportProjectLocationForm" method="post" action="">
                    <input type="hidden" name="workerSelected" id="workerSelected" value="" />
                    <input type="hidden" name="projectSelected" id="projectSelected" value="" />
                    <input type="hidden" name="exportType" id="exportType" value="" />
                    <input type="hidden" name="export" id="export" value="" />
                    
                    <div class="well sidebar-nav span12" style="margin-left: 0px;" >
                        <h4 style="margin: 0px">Search Filter</h4>

                        <hr style="margin: 2px 0;">
                        <br/>
                        
                        <div style="margin-bottom: 10px;"> 
                            <div class="span12" style="margin-left: 0px;">
                                <label for="project" style="font-weight: bold;">Select Project : </label>
                                <select name="project" id="project" required>
                                    <option value="0" <?php if (!isset($inputs)){ ?> selected="selected" <?php } ?> >*** Select Project ***</option>
                                    <?php 
                                    foreach ($projects as $project1){?>

                                    <option value="<?php echo $project1->getIdproject(); ?>" <?php if (isset($inputs) && ($project1->getIdproject() == $inputs->getProjectid())){ ?> selected="selected" <?php } ?> ><?php echo $project1->getProjectname(); ?></option>
                                   <?php }
                                    ?>
                                </select>
                                <label for="worker" style="margin-left: 15px; font-weight: bold;">Select Worker : </label>
                                <select name="worker" id="worker" required>
                                    <option value="0" <?php if (!isset($inputs)){ ?> selected="selected" <?php } ?> >*** Select Worker ***</option>
                                    <?php 
                                    foreach ($workers as $worker){?>
                                    <option value="<?php echo $worker->getIdworker(); ?>" <?php if (isset($inputs) && ($worker->getIdworker() == $inputs->getWorkerid())){ ?> selected="selected" <?php } ?> ><?php echo $worker->getName(); ?></option>
                                   <?php }
                                    ?>
                                 </select>

                                <label for="date" style="margin-left: 15px;  font-weight: bold;">Select Start Date : </label>
                                <input name="startdate" id="startdate" type="date" value="<?php if (isset($inputs) && ($inputs->getStartdate() != '') ) { echo $inputs->getStartdate(); } else { echo date("Y-m-d"); ?><?php } ?>" min="2015-06-22" />

                                <label for="date" style="margin-left: 15px;  font-weight: bold;">Select End Date : </label>
                                <input name="enddate" id="enddate" type="date" value="<?php if (isset($inputs) && ($inputs->getEnddate() != '') ) { echo $inputs->getEnddate(); } else {  $date = new DateTime(); $date->add(new DateInterval('P1D')); echo $date->format('Y-m-d'); ?><?php } ?>" min="2015-06-22" />
                                
                                <label for="radius" style="padding-left: 15px;  font-weight: bold;">Proximity (km) : </label>
                                <input name="radius" id="radius" type="number" step="0.01" value="<?php if (isset($inputs) && ($inputs->getRadius() != '') ) { echo $inputs->getRadius(); } else { echo '0.00'; ?><?php } ?>" min="0" required style="max-width: 60px;" title="'0' for Exact location"/>
                            </div>
                            
                            <div class="span12" style="text-align: right; margin-top: 30px;  margin-left: 0px;">

                                <button name="search" value="search" type="submit" class="btn btn-primary" style="margin-left: 200px;" onclick="javascript:readWorkerValues();">Submit</button>
                                <button type="cancel" class="btn" style="margin-left: 10px">Cancel</button>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="hero-unit" style="background-color: #FFFFFF; min-height: 400px; padding: 20px 0px">
                            
                            <?php
                    
                            if ($action['result'] == 'success') { ?>
                            
                                <div style="display: inline-block"><h4 style="margin: 0px">Search Results</h4></div>
                            
                                <div style="display: inline-block;float: right;margin-right: 120px;">
                                    <div class="btn-group">
                                        <a class="btn btn-danger" href="#">&nbsp;&nbsp;<i class="icon-download-alt icon-white"></i> &nbsp;&nbsp;Export Report &nbsp;&nbsp;</a>
                                        <a class="btn btn-danger dropdown-toggle" data-toggle="dropdown" href="#">&nbsp;&nbsp;<span class="caret"></span>&nbsp;&nbsp;</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript:handleExports('CSV')" ><div style="display: inline-block;margin-bottom: -8px;" class="file-icon" data-type="csv"></div> <div style="display: inline-block; margin-bottom: 5px;">CSV (Comma Separated Values)</div></a></li>
                                            <li><a href="javascript:handleExports('XLS')"><div style="display: inline-block;margin-bottom: -8px;" class="file-icon" data-type="xls"></div> <div style="display: inline-block; margin-bottom: 5px;">XLS (Microsoft Excel old formats)</div></a></li>
                                            <li><a href="javascript:handleExports('XLSX')"><div style="display: inline-block;margin-bottom: -8px;" class="file-icon" data-type="xlsx"></div> <div style="display: inline-block; margin-bottom: 5px;">XLSX (Microsoft Excel new formats)</div></a></li>
                                            <!-- <li><a href="javascript:handleExports('ODS')"><div style="display: inline-block;margin-bottom: -8px;" class="file-icon" data-type="ods"></div> <div style="display: inline-block; margin-bottom: 5px;">.ODS ( Google Spread Sheet)</div></a></li> -->
                                        </ul>
                                    </div>                                
                                </div>
                                
                                <hr style="margin: 15px 0;">
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
                                
                                <div class="span12" style="margin-left: 0px">
                                    <h5>Worker(s) Details : </h5>
                                    <table class="table table-bordered table-striped" style="font-size: 14px;">
                                        <thead>
                                            <tr class="label-info" style="color: #FFFFFF">
                                                <th width="35%">Worker</th>
                                                <th width="15%" style="background-color: #AD3A4F;text-align: center">Total Time Spent <font style="color: #FFFFFF;font-size:17px;">*</font></th>
                                                <th width="35%">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $projectLocationSummary->getWorkerName();?></td>
                                                <td style="background-color: #E7D2D6;font-weight: bold;font-size: 18px;text-align: center"><?php echo $projectLocationSummary->getTotalspent();?></td>
                                                <td><?php echo $projectLocationSummary->getStartday() . '<b>&nbsp; To &nbsp;</b>' . $projectLocationSummary->getEndday();?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <font style="font-size:14px; font-style: italic "><font style="color: #FF0000;font-size:17px;">*</font>Total time spent is calculated by also considering the fact that worker may be on different locations during this period and those 
                                    timestamps should not be included, thus, this calculation only includes the times when worker was exactly in the selected location or  in the given proximity </font>
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
    
    $workerid = mysql_real_escape_string($_POST['worker']);
    $projectid = mysql_real_escape_string($_POST['project']);
    $projectname = mysql_real_escape_string($_POST['projectSelected']);
    
    $startdate = mysql_real_escape_string($_POST['startdate']);
    $enddate = mysql_real_escape_string($_POST['enddate']);
    $radius = mysql_real_escape_string($_POST['radius']);
    $workerSelected = mysql_real_escape_string($_POST['workerSelected']);
    
    if (empty($projectid) || $projectid == "0"){ $action['result'] = 'error'; array_push($text,'Please select Project'); }
    if ($workerid == "0"){$action['result'] = 'error'; array_push($text,'Please select Worker');}
    if (empty($workerSelected)){  $action['result'] = 'error'; array_push($text,'Please select Worker'); }
    if (empty($startdate)){ $action['result'] = 'error'; array_push($text,'Start Date is required'); }
    if (empty($enddate)){ $action['result'] = 'error'; array_push($text,'End Date is required'); }
    //if (empty($radius)){ $action['result'] = 'error'; array_push($text,'Please enter Radius'); }
    
    if ($startdate >= $enddate){
        $action['result'] = 'error'; array_push($text,'End Date should be greator then the Start Date.');
    }
    
    $inputs->setWorkerid($workerid);
    $inputs->setWorkername($workerSelected);
    $inputs->setProjectid($projectid);
    $inputs->setProjectname($projectname);
    $inputs->setStartdate($startdate);
    $inputs->setEnddate($enddate);
    $inputs->setRadius($radius);
    
}

?>
