<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Worker.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once '../functions.php';
include_once 'sessionMgmt.php';


date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 


$action = array();
$action['result'] = null;
$text = array();
$message = NULL;

$adminMgmtDAO = new AdminMgmtDAO();
$allWorkers = NULL;
$inputs = new AdminInputs();

$flag = $adminMgmtDAO->fetchAllWorkerDetails();

if (!$flag){
    $action['result'] = 'error'; 
    array_push($text,$adminMgmtDAO->getError());
    $action['text'] = $text;
}
else{
    $allWorkers = $adminMgmtDAO->getAllWorkers();
}
    
if (isset($_POST['submit'])){

    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $saveFlag = $adminMgmtDAO->saveRetrieveLocationRequest($inputs);

        if ($saveFlag){
            $action['result'] = 'success'; 
            array_push($text,'Manual Retreival of Missed Location History Records is saved successfully.');
            $action['text'] = $text;
        }
        else{
            $action['result'] = 'error'; 
            array_push($text,$adminMgmtDAO->getError());
            $action['text'] = $text;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Admin Panel &middot; Retrieve Location History</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        
        <link href="../css/bootstrap.css" rel="stylesheet">
        <link href="../css/bootstrap-responsive.css" rel="stylesheet">
        <link href="../css/docs.css" rel="stylesheet">
        <link href="../js/google-code-prettify/prettify.css" rel="stylesheet">
        
        <style type="text/css">
            
            @media (max-width: 980px) {
              /* Enable use of floated navbar text */
              .navbar-text.pull-right {
                float: none;
                padding-left: 5px;
                padding-right: 5px;
              }
            }
            .table-heading {
                background-color: #84BBD6;
                color: #FFFFFF;
                font-weight: bold;
                width: 35%
            }
            .close {
                font-size: 30px;
                color: #FF0000;
                opacity: .5;
             }
             .hero-unit {
                 line-height: normal;
             }
             .form-horizontal {
                margin: 0px auto 20px;
                max-width: 800px;
              }
              .form-horizontal input[type="text"],
              .form-horizontal input[type="date"],
              .form-horizontal select,
              .form-horizontal textarea {
                font-size: 15px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
              }
              .form-horizontal label{
                font-size: 15px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
              }
        </style>

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="../js/html5shiv.js"></script>
        <![endif]-->
        
        <script>
            
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
            
        </script>
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">Retrieve Missed Location History </h1>
              <p class="lead" style="font-size:17px">Log the request to load the records of location history for missing date </p>
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
        
        <?php
            if (!empty($action['result']) && $action['result'] == 'success' && !empty($action['text'])){  ?>
                <div class="alert alert-success" style="margin-bottom: 0px;padding-bottom: 0px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo show_successMessages($action); ?>
                </div>
          <?php  }
          ?>
        
        <div class="container" style="padding-top: 30px; min-height: 600px ">
                
            <form class="form-horizontal" id="retreiveLocationForm" name="retreiveLocationForm" method="post" action="">
                <input type="hidden" name="workerSelected" id="workerSelected" value="" />
                
                <?php
                    if (!empty($action['result']) && $action['result'] == 'success'){  ?>
                        <div class="alert alert-info">

                            <p class="lead"><h4 style="color: #1886AA;text-align: center">
                                Request for Retrieval of Missed date of location history is saved for the selected worker.
                                <br/><br/>This request will be processed in few minutes.</h4></p>                        
                        </div>
                        <br/>
                <?php  } ?>
                
                <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">

                    <legend style="border-bottom: 2px solid #e5e5e5;">Provide Request Details</legend>

                    <div class="control-group">
                        <label class="control-label" for="worker" style="font-weight: bold;">Worker Name <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                        <div class="controls">
                            <select name="worker" id="worker" required style="width: 320px;margin-bottom: 0px;">
                                <option value="0" selected="selected" >*** Select Worker ***</option>
                                <?php 
                                foreach ($allWorkers as $worker){?>
                                <option value="<?php echo $worker->getIdworker(); ?>" ><?php echo $worker->getName(); ?></option>
                               <?php }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="date" style="font-weight: bold;">Date <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                        <div class="controls">
                            <input name="date" id="date" type="date" value="<?php echo date("Y-m-d"); ?>" min="2015-06-22" required/>
                        </div>
                    </div>

                    <br/><br/>

                    <div class="control-group">
                        <div class="controls">
                            <button name="submit" value="submit" type="submit" class="btn btn-primary"style="margin-left: 0px;" onclick="javascript:readWorkerValues();">Log Request</button>
                            <button type="button" class="btn" style="margin-left: 20px" onclick="javascript:clearForm();">Cancel</button>
                        </div>
                    </div>
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
        
        <script>
            
            function clearForm(){
                $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
                $(':checkbox, :radio').prop('checked', false);
            }
        </script>

    </body>
</html>

<?php

function preventSQLInjectionAndValidate(AdminInputs $inputs){
    global $action, $text;
    
    $workerid = mysql_real_escape_string($_POST['worker']);  
    $workerSelected = mysql_real_escape_string($_POST['workerSelected']);
    
    $date = mysql_real_escape_string($_POST['date']);

    if ($workerid == "0"){$action['result'] = 'error'; array_push($text,'Please select Worker');}
    if (empty($date)){ $action['result'] = 'error'; array_push($text,'Date is required'); }
    
    $inputs->setWorkerid($workerid);
    $inputs->setWorkerName($workerSelected);
    $inputs->setDate($date);
    
}

?>
