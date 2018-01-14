<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Project.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistory.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'DistanceSummary.php';
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

$distanceSummary = new DistanceSummary();

$inputs = new Inputs();

if (!$flag1){
    $action['result'] = 'error'; 
    array_push($text,$locationHistoryDAO->getError());
    $action['text'] = $text;
}

if (isset($_POST['search'])){
    
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        //Retrieve location history records based on the distances
        $flag = $locationHistoryDAO->fetchLocationHistoryForDistanceReport($inputs, $user);
        
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
            
            $distanceSummary = $locationHistoryDAO->getDistanceSummary();
            $_SESSION['reportData'] = $distanceSummary;
            $_SESSION['reportInputs'] = $inputs;
        }
        
    }
        
}
else if (isset($_POST['export']) && $_POST['export'] == "true"){
    
    header('location: downloadReport.php?report=distanceSummary&type='.$_POST['exportType']);
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
            }
            
            function handleExports(type){
                document.getElementById("export").value = "true";
                document.getElementById("exportType").value = type;
                
                document.forms['reportdistanceForm'].submit();
            }
            
        </script>
        
    </head>

    <body>
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Worker(s) Location History - Distance based Summary</h1>
              <p class="lead" style="font-size:17px">Measure the distances in kms between given dates of workers (including only working hours) </p>
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
                
                <form class="form-inline" id="reportdistanceForm" name="reportdistanceForm" method="post" action="">
                    <input type="hidden" name="workerSelected" id="workerSelected" value="" />
                    <input type="hidden" name="exportType" id="exportType" value="" />
                    <input type="hidden" name="export" id="export" value="" />
                    
                    <div class="well sidebar-nav" >
                        <h4 style="margin: 0px">Search Filter</h4>

                        <hr style="margin: 2px 0;">
                        <br/>
                        
                        <div style="margin-bottom: 10px;"> 
                            <label for="worker" style="font-weight: bold;">Select Worker : </label>
                            <select name="worker" id="worker" required>
                                <option value="0" <?php if (!isset($inputs)){ ?> selected="selected" <?php } ?> >*** Select Worker ***</option>
                                <?php 
                                foreach ($workers as $worker){?>
                                <option value="<?php echo $worker->getIdworker(); ?>" <?php if (isset($inputs) && ($worker->getIdworker() == $inputs->getWorkerid())){ ?> selected="selected" <?php } ?> ><?php echo $worker->getName(); ?></option>
                               <?php }
                                ?>
                             </select>

                            <label for="date" style="margin-left: 75px;  font-weight: bold;">Select Start Date : </label>
                            <input name="startdate" id="startdate" type="date" value="<?php if (isset($inputs) && ($inputs->getStartdate() != '') ) { echo $inputs->getStartdate(); } else { echo date("Y-m-d"); ?><?php } ?>" min="2015-06-22" />

                            <label for="date" style="margin-left: 75px;  font-weight: bold;">Select End Date : </label>
                            <input name="enddate" id="enddate" type="date" value="<?php if (isset($inputs) && ($inputs->getEnddate() != '') ) { echo $inputs->getEnddate(); } else {  $date = new DateTime(); $date->add(new DateInterval('P1D')); echo $date->format('Y-m-d'); ?><?php } ?>" min="2015-06-22" />
                                
                            <!-- <label for="starttime" style="padding-left: 20px;font-weight: bold;">Select Start Time : </label>
                            <input name="starttime" id="starttime" type="time" value="<?php if (isset($inputs) && ($inputs->getStarttime() != '') ) { echo $inputs->getStarttime(); } else { echo '00:00'; ?><?php } ?>" />

                            <label for="endtime" style="padding-left: 20px;font-weight: bold;">Select End Time : </label>
                            <input name="endtime" id="endtime" type="time" value="<?php if (isset($inputs) && ($inputs->getEndtime() != '') ) { echo $inputs->getEndtime(); } else { echo '01:00'; ?><?php } ?>" /> -->

                            <button name="search" value="search" type="submit" class="btn btn-primary" style="margin-left: 140px" onclick="javascript:readWorkerValues();">Submit</button>
                            <button type="cancel" class="btn" style="margin-left: 10px">Cancel</button>
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
                                    <h5>Worker(s) Details : </h5>
                                    <table class="table table-bordered table-striped" style="font-size: 14px;" >
                                        <thead>
                                            <tr class="label-info" style="color: #FFFFFF; " >
                                                <th width="15%">Worker</th>
                                                <th width="10%" style="background-color: #AD3A4F;">Distance [In Kms]<font style="color: #FFFFFF;font-size:17px;">*</font></th>
                                                <th width="28%">Begin Location</th>
                                                <th>End Location</th>
                                                <th width="11%" style="background-color: #69757A;">Working Days</th>
                                                <th width="9%" style="background-color: #69757A;">Working Hours</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $distanceSummary->getWorkerName();?></td>
                                                <td style="background-color: #E7D2D6;font-weight: bold;font-size: 18px;text-align: center"><?php echo $distanceSummary->getDistance();?></td>
                                                <td><?php echo $distanceSummary->getStartlocation()?></td>
                                                <td><?php echo $distanceSummary->getEndlocation()?></td>
                                                <td style="background-color: #D6D6D6;"><?php echo $distanceSummary->getWorkingDays();?></td>
                                                <td style="background-color: #D6D6D6;border-left-color: #888E96 "><?php echo $distanceSummary->getWorkingHours();?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <font style="font-size:14px; font-style: italic "><font style="color: #FF0000;font-size:17px;">*</font>Total Distance is calculated by including only working hours. </font>
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
    $workerSelected = mysql_real_escape_string($_POST['workerSelected']);
    
    $startdate = mysql_real_escape_string($_POST['startdate']);
    $enddate = mysql_real_escape_string($_POST['enddate']);

    if ($workerid == "0"){$action['result'] = 'error'; array_push($text,'Please select Worker');}
    if (empty($startdate)){ $action['result'] = 'error'; array_push($text,'Start Date is required'); }
    if (empty($enddate)){ $action['result'] = 'error'; array_push($text,'End Date is required'); }
    if (empty($workerSelected)){  $action['result'] = 'error'; array_push($text,'Please select Worker'); }
    
    if ($startdate >= $enddate){
        $action['result'] = 'error'; array_push($text,'End Date should be greator then the Start Date.');
    }
    
    $inputs->setWorkerid($workerid);
    $inputs->setStartdate($startdate);
    $inputs->setEnddate($enddate);
    $inputs->setWorkername($workerSelected);
}

?>
