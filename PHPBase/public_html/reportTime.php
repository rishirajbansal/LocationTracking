<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
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
$flag = $locationHistoryDAO->fetchAllWorkers($user);
$workers = $locationHistoryDAO->getWorkers();

$locationHistoryWorkerTimeBased = NULL;

if ($flag){
}
else{
    $action['result'] = 'error'; 
    array_push($text,$locationHistoryDAO->getError());
    $action['text'] = $text;
}

   
if (isset($_POST['search'])){
    
    $inputs = new Inputs();
    
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;

    if ($action['result'] != 'error'){
        
        //Retrieve location history records based on the worker name
        $flag = $locationHistoryDAO->fetchLocationHistoryBasedOnTime($inputs, $user);
        
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
            
            $locationHistoryWorkerTimeBased = $locationHistoryDAO->getLocationHistoryWorkerTimeBased();
            $_SESSION['reportData'] = $locationHistoryWorkerTimeBased;
            $_SESSION['reportInputs'] = $inputs;
            
        }
        
    }
}
else if (isset($_POST['export']) && $_POST['export'] == "true"){
    
    header('location: downloadReport.php?report=timeSummary&type='.$_POST['exportType']);
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
                
                document.forms['reporttimeForm'].submit();
            }
            
            function disableCoverage(){
                if (document.getElementById("detail").checked){
                    document.getElementById("area").disabled = true;
                    document.getElementById("timegap").disabled = true;
                }
                else{
                    document.getElementById("area").disabled = false;
                    document.getElementById("timegap").disabled = false;
                }
                
            }
            
        </script>
        
    </head>

    <body>
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Worker Location History - Time based Summary</h1>
              <p class="lead" style="font-size:17px">Report to get the location history based on the selected time interval</p>
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
                
                <form class="form-inline" id="reporttimeForm" name="reporttimeForm" method="post" action="">
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

                            <label for="date" style="padding-left: 20px;font-weight: bold;">Select Date : </label>
                            <input name="date" id="date" type="date" value="<?php if (isset($inputs) && ($inputs->getDate() != '') ) { echo $inputs->getDate(); } else { echo date("Y-m-d"); ?><?php } ?>" min="2015-06-22" />

                            <label for="starttime" style="padding-left: 20px;font-weight: bold;">Select Start Time : </label>
                            <input name="starttime" id="starttime" type="time" value="<?php if (isset($inputs) && ($inputs->getStarttime() != '') ) { echo $inputs->getStarttime(); } else { echo '00:00'; ?><?php } ?>" />

                            <label for="endtime" style="padding-left: 20px;font-weight: bold;">Select End Time : </label>
                            <input name="endtime" id="endtime" type="time" value="<?php if (isset($inputs) && ($inputs->getEndtime() != '') ) { echo $inputs->getEndtime(); } else { echo '01:00'; ?><?php } ?>" />

                            <button name="search" value="search" type="submit" class="btn btn-primary" style="margin-left: 40px" onclick="javascript:readWorkerValues();">Submit</button>
                            <button type="cancel" class="btn" style="margin-left: 10px">Cancel</button>
                            
                            <br/><br/>
                            
                            <label class="checkbox" style="font-weight: bold;">
                                <input name="detail" id="detail" type="checkbox" style="width: 25px;" <?php if (isset($inputs) && ($inputs->getIsDetailView() == 2) ) { ?> checked  <?php } ?> value="detail" onclick="disableCoverage()" />
                                Detail View with all times
                            </label>
                            
                            <br/><br/>
                            
                            <div style="display:inline-block;">
                                <span class="label" style="padding: 12px 5px;font-size: 14px">Allowable Ignorance for single stop consideration </span>
                            </div>
                            
                            <div style="width: 30%; padding: 5px;border-radius: 0px 5px 5px 0px;display: inline-block; margin-left: -4px;background-color: #DEDEDE;">
                                
                                
                                <label for="area" style="padding-left: 15px;  font-weight: bold;">Area (m's) : </label>
                                <input name="area" id="area" type="number" step="5" value="<?php if (isset($inputs) && ($inputs->getArea() != '') ) { echo $inputs->getArea(); } else { echo '300'; ?><?php } ?>" style="max-width: 100px;" min="0" />
                                
                                <label for="timegap" style="padding-left: 15px;  font-weight: bold;">Timegap (mins) : </label>
                                <input name="timegap" id="timegap" type="number" step="1" value="<?php if (isset($inputs) && ($inputs->getTimegap() != '') ) { echo $inputs->getTimegap(); } else { echo '5'; ?><?php } ?>" style="max-width: 60px;" min="0" />
                            </div>
                        </div>

                    </div>
                    
                    <div>
                        <div class="hero-unit" style="background-color: #FFFFFF; min-height: 400px; padding: 20px">
                            
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

                                <div class="span5" style="margin-left: 0px">
                                    <table class="table table-striped" style="font-size: 14px;">
                                        <thead>
                                            <tr class="label-info" style="color: #FFFFFF">
                                                <th style="text-align: center;" width="7%">#</th>
                                                <th width="15%">Time</th>
                                                <th width="30%">Coordinates</th>
                                                <th>Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $ctr = 1;
                                                $rowToggle=0;
                                                foreach ($locationHistoryWorkerTimeBased as $locationHistory) { ?>
                                                <tr>
                                                       <td style="text-align: center;background-color: #EBEBEB;"><?php echo $ctr;?></td>
                                                       <td style="background-color: #EBEBEB;"><?php echo $locationHistory->getTime();?></td>
                                                       <td style="background-color: #EBEBEB;"><?php echo $locationHistory->getLatitude() . ' / ' . $locationHistory->getLongitude();?></td>
                                                       <td style="background-color: #EBEBEB;"><?php echo $locationHistory->getLocation();?></td>
                                                   </tr>
                                            <?php 
                                                    if ($locationHistory->getStopDuration() != ''){ ?>
                                                        <tr>
                                                            <td colspan="4" style="color: #2589C0;text-align: center;border-top: 3px solid #FFF;border-bottom: 3px solid #FFF;">Stop Duration: <strong><?php echo $locationHistory->getStopDuration();?></strong></td>
                                                        </tr>
                                                    <?php }
                                                    $ctr+=1 ; 
                                                 }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="span7" >
                                    <?php
                                          $filename = Config::$xmlFile . '.xml';
                                          include("mapLocationMarkers.php"); 
                                    ?>
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
    $date = mysql_real_escape_string($_POST['date']);
    $starttime = mysql_real_escape_string($_POST['starttime']);
    $endtime = mysql_real_escape_string($_POST['endtime']);
    $workerSelected = mysql_real_escape_string($_POST['workerSelected']);
    
    $area = '0';
    if (!empty($_POST['area'])){
        $area = mysql_real_escape_string($_POST['area']);
    }
    $timegap = '0';
    if (!empty($_POST['timegap'])){
        $timegap = mysql_real_escape_string($_POST['timegap']);
    }
    
    $detail = 1;
    if (!empty($_POST['detail'])){
        $detail = 2;
    }

    if ($workerid == "0"){$action['result'] = 'error'; array_push($text,'Please select Worker');}
    if(empty($date)){ $action['result'] = 'error'; array_push($text,'Date is required'); }
    if(empty($starttime)){ $action['result'] = 'error'; array_push($text,'Please Select Start Time'); }
    if(empty($endtime)){ $action['result'] = 'error'; array_push($text,'Please Select End Time'); }
    
    if ($starttime >= $endtime){
        $action['result'] = 'error'; array_push($text,'End time should be greator then the start time.');
    }
    
    $inputs->setWorkerid($workerid);
    $inputs->setDate($date);
    $inputs->setStarttime($starttime);
    $inputs->setEndtime($endtime);
    $inputs->setWorkername($workerSelected);
    $inputs->setIsDetailView($detail);
    $inputs->setTimegap($timegap);
    $inputs->setArea($area);
    
}

?>
