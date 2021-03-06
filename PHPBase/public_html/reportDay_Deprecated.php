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
    
if (isset($_POST['submit'])){
    
    $inputs = new Inputs();
    
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        //Retrieve location history records based on the worker(s) date
        $flag = $locationHistoryDAO->fetchLocationHistoryForWorkersBasedOnDay($inputs);
        
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
            
            $locationHistoryWorkersDayBased = $locationHistoryDAO->getLocationHistoryWorkersDayBased();
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
            }
        </script>
        
    </head>

    <body>
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Worker(s) Location History - Day based Summary</h1>
              <p class="lead" style="font-size:17px">Report to get ALL the history of all the locations based on the selected day</p>
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
                
                <form class="form-inline" id="reportdayForm" name="reportdayForm" method="post" action="">
                    <input type="hidden" name="workersSelected" id="workersSelected" value="" />
                    
                    <div class="well sidebar-nav" >
                        <h4 style="margin: 0px">Search Filter</h4>

                        <hr style="margin: 2px 0;">
                        <br/>
                        
                        <div style="margin-bottom: 10px;"> 
                            <label for="worker" style="margin-left: 50px;  font-weight: bold;">Select Worker : </label>
                            <select name="worker" id="worker" required multiple="multiple" size="3" style="min-width: 300px;" >
                                <?php 
                                $temp = '';
                                if (isset($inputs)){ $temp = $inputs->getWorkeridList(); }
                                foreach ($workers as $worker){?>
                                <option value="<?php echo $worker->getIdworker(); ?>" <?php if (isset($inputs) && !empty($temp) && (in_array($worker->getIdworker(), $inputs->getWorkeridList())) ){ ?> selected="selected" <?php } ?> ><?php echo $worker->getName(); ?></option>
                               <?php }
                                ?>
                             </select>
                            <span class="help-inline">(You can select more then one worker)</span>

                            <label for="date" style="margin-left: 170px;  font-weight: bold;">Select Date : </label>
                            <input name="date" id="date" type="date" value="<?php if (isset($inputs) && ($inputs->getDate() != '') ) { echo $inputs->getDate(); } else { echo date("Y-m-d"); ?><?php } ?>" min="2015-06-22" />

                            <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 210px" onclick="javascript:readWorkerValues();">Submit</button>
                            <button type="cancel" class="btn" style="margin-left: 10px">Cancel</button>
                        </div>

                    </div>
                    
                    <div>
                        <div class="hero-unit" style="background-color: #FFFFFF; min-height: 400px; padding: 20px">
                            
                            <?php
                    
                            if ($action['result'] == 'success') { ?>
                            
                                <h4 style="margin: 0px">Search Results</h4>

                                <hr style="margin: 2px 0;">
                                <br/>

                                <div class="span12" style="margin-left: 0px">
                                    <table class="table table-bordered table-striped" style="font-size: 14px;">
                                        <thead>
                                            <tr class="label-info" style="color: #FFFFFF">
                                                <th width="12%">Worker</th>
                                                <th width="8%">Time Interval</th>
                                                <th>Locations on Map</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $ctr = 0;
                                                $rowToggle=0;
                                                $class='';
                                                foreach ($locationHistoryWorkersDayBased as $record) { 
                                                        $workerName = $record['workername'];
                                                        $workerRecord = $record['workerRecords'];
                                                        if ($rowToggle) { $rowToggle=0; $class='warning'; } else { $class='plain'; $rowToggle=1; }
                                                    ?>
                                                <tr class="<?php echo $class; ?>">
                                                       <td rowspan="2"><?php echo $workerName;?></td>
                                                       <td><?php echo $workerRecord[0]['timeInterval'];?></td>
                                                       <?php $filename = $workerRecord[0]['filename'];
                                                             $fileGen = $workerRecord[0]['xmlFile'];
                                                              if (!empty($fileGen)){?>
                                                                 <td><iframe align="center" style="  height: 350px;width: 1300px;" src="mapMultipleLocationMarker.php?filename=<?php echo $filename; ?>"  frameborder="yes" scrolling="yes"> </iframe></td>
                                                             <?php }
                                                             else{ ?>
                                                                <td>No records found for this time interval</td>
                                                             <?php }
                                                       ?>
                                                </tr>
                                                
                                                <tr class="<?php echo $class; ?>">
                                                        
                                                        <td><?php echo $workerRecord[1]['timeInterval'];?></td>
                                                        <?php $filename = $workerRecord[1]['filename'];
                                                             $fileGen = $workerRecord[1]['xmlFile'];
                                                              if (!empty($fileGen)){?>
                                                                 <td><iframe align="center" style="  height: 350px;width: 1300px;" src="mapMultipleLocationMarker.php?filename=<?php echo $filename; ?>"  frameborder="yes" scrolling="yes"> </iframe></td>
                                                             <?php }
                                                             else{ ?>
                                                                <td>No records found for this time interval</td>
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
    
    $workersSelected = mysql_real_escape_string($_POST['workersSelected']); 
    $date = mysql_real_escape_string($_POST['date']);
    
    if(empty($date)){ $action['result'] = 'error'; array_push($text,'Date is required'); }
    if (empty($workersSelected)){
        $action['result'] = 'error'; array_push($text,'Please select Worker');
    }
    else{
        $inputs->setWorkeridList(explode(",", $workersSelected));
    }
    
    $inputs->setDate($date);
}

?>
