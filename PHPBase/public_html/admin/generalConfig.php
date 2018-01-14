<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'SystemConfiguration.php';
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
$allConfig = NULL;
$inputs = new AdminInputs();

$flag = $adminMgmtDAO->fetchGeneralConfig();

if (!$flag){
    $action['result'] = 'error'; 
    array_push($text,$adminMgmtDAO->getError());
    $action['text'] = $text;
}
else{
    $allConfig = $adminMgmtDAO->getGeneralConfig();
}

if (isset($_POST['submit'])){

    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $updateFlag = $adminMgmtDAO->updateGeneralConfig($inputs);

        if ($updateFlag){

            //Fetch the updated config
            $flag = $adminMgmtDAO->fetchGeneralConfig();
            if ($flag){
                $allConfig = $adminMgmtDAO->getGeneralConfig();

                $action['result'] = 'success'; 
                array_push($text,'System configuration updated successfully.');
                $action['text'] = $text;
            }
            else{
                $action['result'] = 'error'; 
                array_push($text,$adminMgmtDAO->getError());
                $action['text'] = $text;
            }
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
        <title>Admin Panel &middot; System Configuration</title>
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
            @media (min-width: 1700px) { 
                .actions{
                    width: 17%;
                }
            }
            @media (min-width: 1500px) and (max-width: 1600px) { 
                .actions{
                    width: 20%;
                }
            }
            @media (min-width: 1300px) and (max-width: 1490px) { 
                .actions{
                    width: 24%;
                }
            }
            @media (min-width: 990px) and (max-width: 1290px) { 
                .actions{
                    width: 35%;
                }
            }
            @media (min-width: 768px) and (max-width: 979px) { 
                .actions{
                    width: 27%;
                }
            }
            .table-heading {
                background-color: #84BBD6;
                color: #FFFFFF;
                font-weight: bold;
                width: 35%
            }
            .table tbody tr.warning > td {
                background-color: #fcf8e3 !important;
            }
            table tbody tr.plain > td {
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
          <script src="../js/html5shiv.js"></script>
        <![endif]-->
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">System - General Configuration</h1>
              <p class="lead" style="font-size:17px">General Configuration settings for Backend Program</p>
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
        
        <div class="container-fluid " style="padding-top: 30px; min-height: 600px ">
                
            <form class="form-inline" id="generalConfigForm" name="generalConfigForm" method="post" action="">
                
                 <?php
                    if (!empty($action['result']) && $action['result'] == 'success'){  ?>
                        <div class="alert alert-warning" style="margin-left: 10%;margin-right: 10%;text-align: center">

                            <p class="lead"><h4 style="color: #FF0000;">
                                There is no need to restart the system, the backend system will automatically be updated with the latest configuration changes.
                                <br/><br/>It will take few minutes by the system to have the new changes in effect.</h4></p>                        
                        </div>
                        <br/>
                <?php  } ?>
                
                <table class="table table-bordered table-striped" style="font-size: 14px;">
                    <thead>
                        <tr class="label-info" style="color: #FFFFFF;background-color: #C99A71">
                            <th width="20%">Name</th>
                            <th width="20%">Code Name</th>
                            <th width="25%">Value</th>
                            <th width="35%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($allConfig)){
                                $ctr = 1;
                                $rowToggle=0;
                                $config = new SystemConfiguration();
                                foreach ($allConfig as $config) {
                                    ?>
                                    <tr class="warning">
                                        <td style="vertical-align: middle;"><strong><?php echo $config->getName();?></strong></td>
                                        <td style="vertical-align: middle;"><?php echo $config->getCodeName();?></td>
                                        <td>
                                            <div class="control-group">
                                                <div class="controls">
                                                    <input name="<?php echo 'c_'.$config->getCodeName();?>" id="<?php echo 'c_'.$config->getCodeName();?>" type="<?php if ($config->getType()=='numeric'){ ?>number<?php } else {?>text<?php } ?>" style="width: 95%;font-weight: bold" value="<?php echo $config->getValue();?>" pattern="<?php if ($config->getType()=='boolean'){ ?>(true|false)<?php } else {?>*<?php } ?>" required/>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;"><?php echo $config->getDescription();?></td>
                                    </tr>
                                    
                        <?php 
                            $ctr+=1 ; 
                             }
                            }
                        ?>
                    </tbody>
                </table>
                
                <div class="control-group">
                    <div class="controls" style="float:right;margin-top:30px">
                        <button name="submit" value="submit" type="submit" class="btn btn-danger"style="margin-left: 0px;">Update</button>
                        <button type="button" class="btn" style="margin-left: 20px" onclick="javascript:reset();">Cancel</button>

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

    </body>
</html>

<?php

function preventSQLInjectionAndValidate(AdminInputs $inputs){
    global $action, $text;
    
    $newConfig = array();
    
    foreach ( $_POST as $key => $value ) {
        if (startsWith($key, 'c_')){
            $temp = substr($key, strlen('c_'));
            $newConfig[$temp] = mysql_real_escape_string($value);
            
            if (empty($value)){ $action['result'] = 'error'; array_push($text, $temp.' is required'); }
        }
       
    }
    
    $inputs->setNewGeneralConfig($newConfig);
    
}

function startsWith($haystack, $needle) {
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}


?>
