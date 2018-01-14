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
$worker = new Worker();
$workerDetails = NULL;
$allDomains = NULL;

$isUpdating = FALSE;
$mode = 'New';
$workerMode = $worker->getMode();

if (empty($workerMode)){
    $worker->setMode($mode);
}

$flag = $adminMgmtDAO->fetchAllDomainDetails();

if (!$flag){
    $action['result'] = 'error'; 
    array_push($text,$adminMgmtDAO->getError());
    $action['text'] = $text;
}
else{
    $allDomains = $adminMgmtDAO->getAllDomains();
}
    
if (isset($_POST['submit'])){

    preventSQLInjectionAndValidate($worker);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $saveFlag = $adminMgmtDAO->addWorker($worker);

        if ($saveFlag){

            //Fetch the Worker records
            $flag = $adminMgmtDAO->fetchWorkerDetails($worker);
            if ($flag){
                $workerDetails = $adminMgmtDAO->getWorker();

                $action['result'] = 'success'; 
                if ($worker->getMode() == 'New'){
                    array_push($text,'Worker record is added successfully.');
                }
                else{
                    array_push($text,'Worker record is updated successfully.');
                }
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
else if (isset($_GET['update'])){
    //echo 'update';
    $workerid = $_GET['workerid'];
    $mode = 'update';
    $worker->setMode($mode);
   
    if (!empty($workerid)){
        $worker->setIdworker($workerid);
        $flag = $adminMgmtDAO->fetchWorkerDetails($worker);
        
        if ($flag){
            $workerDetails = $adminMgmtDAO->getWorker();
            $worker = $workerDetails;
            $worker->setMode($mode);

            $isUpdating = TRUE;
        }
        else{
            $action['result'] = 'error'; 
            array_push($text,$adminMgmtDAO->getError());
            $action['text'] = $text;
        }
    }
    else{
        $action['result'] = 'error'; 
        array_push($text,'Worker Id not received');
        $action['text'] = $text;
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Admin Panel &middot; Worker Management</title>
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
              .form-horizontal input[type="password"],
              .form-horizontal input[type="email"],
              .form-horizontal select {
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
                width: 210px !important;
              }
              .form-horizontal .controls {
                margin-left: 230px;
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
              <h1 style="font-size:27px">
                  <?php
                    if ($worker->getMode() == 'New'){ ?>
                        Add New Worker
                    <?php } else { ?>
                        Update Existing Worker
                    <?php } ?>
                  
              </h1>
              <p class="lead" style="font-size:17px">Add new Worker that will be monitored by the application</p>
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
                
            <form class="form-horizontal" id="addWorkerForm" name="addWorkerForm" method="post" action="">
                <input type="hidden" name="mode" id="mode" value="<?php echo $worker->getMode();  ?>" />
                <input type="hidden" name="workerid" id="workerid" value="<?php echo $worker->getIdworker();  ?>" />
                
                <?php

                if ( $action['result'] != 'success' ||  $isUpdating ) { ?>
                    <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">

                        <legend style="border-bottom: 2px solid #e5e5e5;">Provide Worker Details</legend>
                        
                        <div class="control-group">
                            <label class="control-label" for="domain" style="font-weight: bold;">Domain to Associate with <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <?php
                                if ($worker->getMode() == 'New'){ ?>
                                    <select name="domain" id="domain" required style="width: 420px;margin-bottom: 0px;" >
                                        <option value="0" <?php if (!isset($worker)){ ?> selected="selected" <?php } ?> >*** Select Domain ***</option>
                                        <?php 
                                        foreach ($allDomains as $domain){?>

                                        <option value="<?php echo $domain->getDomainId(); ?>" <?php if (isset($worker) && ($worker->getDomainId() == $domain->getDomainid())){ ?> selected="selected" <?php } ?> ><?php echo $domain->getDomainName(); ?></option>
                                       <?php }
                                        ?>
                                    </select>
                                    <span class="help-block" style="margin-bottom: 15px;">(Domain is not modifiable once the worker is created)</span>
                                <?php }
                                else {
                                    //Updating the Domain is not allowed as the worker records are stroed in locationhistory_<<domain id>> and historyexecutiuon_<<domain id>>, updating the domain will change the table but would leave the records in previous tables whihc can create issues in deleting the domain related to refrential integrity
                                    ?>
                                    <input type="hidden" name="domain" id="domain" value="<?php echo $worker->getDomainId();  ?>" />
                                    <input name="domainname" id="domainname" type="text" style="width: 400px;" value="<?php echo $worker->getDomainname();?>" required readonly="true" />                                    
                                    <!-- <span class="help-block" style="margin-bottom: 15px;">(Not modifiable)</span> -->
                                <?php } ?>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="name" style="font-weight: bold;">Worker Name <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="name" id="name" type="text" style="width: 400px;" value="<?php if (isset($worker) && ($worker->getName() != '') ) { echo $worker->getName(); } ?>" placeholder="Worker Name" required  />
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label class="control-label" for="email" style="font-weight: bold;">Email (Google) <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="email" id="email" type="email" style="width: 400px;" value="<?php if (isset($worker) && ($worker->getEmail() != '') ) { echo $worker->getEmail(); } ?>" placeholder="email@gmail.com" required  />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="password" style="font-weight: bold;">Password (Google) <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="password" id="password" type="password" style="width: 400px;" value="<?php if (isset($worker) && ($worker->getPassword() != '') ) { echo $worker->getPassword(); } ?>" placeholder="Password" required  />
                            </div>
                        </div>

                        <br/><br/>

                        <div class="control-group">
                            <div class="controls">
                                <?php
                                if ($worker->getMode() == 'New'){ ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Save</button>
                                    <button type="button" class="btn" style="margin-left: 20px" onclick="javascript:clearForm();">Cancel</button>
                                <?php } else { ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Update</button>
                                    <a class="btn" href="listWorkers.php" style="margin-left: 20px">Cancel</a>
                               <?php } ?> 
                                
                            </div>
                        </div>
                    </div>
                 <?php } 
                  if ( $action['result'] == 'success' || $isUpdating ) { ?>
                    <div class="hero-unit" style="background-color: #FFFFFF; min-height: 50px; padding: 20px 0px">

                        <h4 style="margin: 0px;border-bottom: 2px solid #B3BFCA;">Saved Worker Details</h4>

                        <!-- <hr style="margin: 2px 0;"> -->
                        <br/>
                        <div style="margin-left: 0px">
                            <table class="table table-bordered" style="font-size: 14px;">
                                <tbody>
                                    <tr>
                                        <td class="table-heading">Name</td>
                                        <td><?php echo $workerDetails->getName()?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Domain</td>
                                        <td><?php echo $workerDetails->getDomainname()?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Email</td>
                                        <td><?php echo $workerDetails->getEmail()?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Password</td>
                                        <td><?php echo $workerDetails->getPassword()?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        <?php 
                        if (!$isUpdating) { ?>
                            <div style="text-align: center">
                                <br/><br/>
                                <a class="btn btn-large btn-success" href="listWorkers.php" >List Workers</a>
                            </div>
                        <?php } ?>
                    </div>
                    
                <?php 
                 }
                ?>

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

function preventSQLInjectionAndValidate(Worker $worker){
    global $action, $text;
    
    $domainid = mysql_real_escape_string($_POST['domain']);
    $workerName = mysql_real_escape_string($_POST['name']);
    $email = mysql_real_escape_string($_POST['email']);
    $password = mysql_real_escape_string($_POST['password']);
    
    if (empty($domainid) || $domainid == "0"){ $action['result'] = 'error'; array_push($text,'Please select Domain'); }
    if (empty($workerName)){ $action['result'] = 'error'; array_push($text,'Worker Name is required'); }
    if (empty($email)){ $action['result'] = 'error'; array_push($text,'Email is required'); }
    if (empty($password)){ $action['result'] = 'error'; array_push($text,'Password is required'); }
    
    //For Update
    $mode = mysql_real_escape_string($_POST['mode']);
    $workerid = mysql_real_escape_string($_POST['workerid']);
    if (!empty($workerid)){
        $worker->setIdworker($workerid);
    }
    $worker->setMode($mode);
    
    
    $worker->setDomainId($domainid);
    $worker->setName($workerName);
    $worker->setEmail($email);
    $worker->setPassword($password);
    
}

?>
