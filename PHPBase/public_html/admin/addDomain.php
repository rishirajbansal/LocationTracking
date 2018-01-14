<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Domain.php';
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
$domain = new Domain();
$domainDetails = NULL;
$isUpdating = FALSE;

$mode = 'New';
$domainMode = $domain->getMode();

if (empty($domainMode)){
    $domain->setMode($mode);
}
    
if (isset($_POST['submit'])){

    preventSQLInjectionAndValidate($domain);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $saveFlag = $adminMgmtDAO->addDomain($domain);

        if ($saveFlag){

            //Fetch the domain records
            $flag = $adminMgmtDAO->fetchDomainDetails($domain);
            if ($flag){
                $domainDetails = new Domain();
                $domainDetails = $adminMgmtDAO->getDomain();

                $action['result'] = 'success'; 
                if ($domain->getMode() == 'New'){
                    array_push($text,'Domain record is added successfully.');
                }
                else{
                    array_push($text,'Domain record is updated successfully.');
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
    $domainid = $_GET['domainid'];
    $mode = 'update';
    $domain->setMode($mode);
    
    if (!empty($domainid)){
        $domain->setDomainId($domainid);
        $flag = $adminMgmtDAO->fetchDomainDetails($domain);
        
        if ($flag){
            $domainDetails = $adminMgmtDAO->getDomain();
            $domain = $domainDetails;
            $domain->setMode($mode);

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
        array_push($text,'Domain Id not received');
        $action['text'] = $text;
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Admin Panel &middot; Domain Management</title>
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
              .form-horizontal input[type="time"],
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
        
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">
                  <?php
                    if ($domain->getMode() == 'New'){ ?>
                        Add New Domain
                    <?php } else { ?>
                        Update Existing Domain
                    <?php } ?>
                  
              </h1>
              <p class="lead" style="font-size:17px">Create Domain to distinguish application data and group them for the intended tenants</p>
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
                
            <form class="form-horizontal" id="addDomainForm" name="addDomainForm" method="post" action="">
                <input type="hidden" name="mode" id="mode" value="<?php echo $domain->getMode();  ?>" />
                <input type="hidden" name="domainid" id="domainid" value="<?php echo $domain->getDomainId();  ?>" />
                
                <?php

                if ( $action['result'] != 'success' ||  $isUpdating ) { ?>
                    <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">

                        <legend style="border-bottom: 2px solid #e5e5e5;">Provide Domain Details</legend>

                        <div class="control-group">
                            <label class="control-label" for="name" style="font-weight: bold;">Domain Name <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="name" id="name" type="text" style="width: 400px;" value="<?php if (isset($domain) && ($domain->getDomainName() != '') ) { echo $domain->getDomainName(); } ?>" placeholder="Domain Name" required  />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="description" style="font-weight: bold;">Description : </label>
                            <div class="controls">
                                <textarea name="description" id="description" rows="7" style="width: 400px;" placeholder="Few lines about the domain"  ><?php if (isset($domain) && ($domain->getRawDesc() != '') ) { echo $domain->getRawDesc(); } ?></textarea>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label class="control-label" for="domain" style="font-weight: bold;">Working Days : </label>
                            <div class="controls">
                                <select name="workDayStart" id="workDayStart" required style="width: 190px;margin-bottom: 0px;" >
                                    <?php 
                                    for ($i = 1; $i <= 7; $i++) {?>

                                    <option value="<?php echo $i; ?>" <?php if ( (isset($domain) && ($domain->getWorkDayStart() == $i)) || ($domain->getWorkDayStart() == NULL && $i == 2) ){ ?> selected="selected" <?php } ?> ><?php echo Domain::$weekdays[$i]; ?></option>
                                   <?php }
                                    ?>
                                </select>
                                &nbsp;&nbsp; To &nbsp;&nbsp;
                                <select name="workDayEnd" id="workDayEnd" required style="width: 190px;margin-bottom: 0px;" >
                                    <?php 
                                    for ($i = 1; $i <= 7; $i++) {?>

                                    <option value="<?php echo $i; ?>" <?php if ( (isset($domain) && ($domain->getWorkDayEnd() == $i)) || ($domain->getWorkDayEnd() == NULL && $i == 6) ){ ?> selected="selected" <?php } ?> ><?php echo Domain::$weekdays[$i]; ?></option>
                                   <?php }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label class="control-label" for="domain" style="font-weight: bold;">Working Hours : </label>
                            <div class="controls">
                                <input name="workTimeStart" id="workTimeStart" type="time" style="width: 120px;" value="<?php if (isset($domain) && ($domain->getWorkTimeStart() != '') ) { echo $domain->getWorkTimeStart(); } else { echo '09:00'; ?><?php } ?>" />
                                &nbsp;&nbsp; To &nbsp;&nbsp;
                                <input name="workTimeEnd" id="workTimeEnd" type="time" style="width: 120px;" value="<?php if (isset($domain) && ($domain->getWorkTimeEnd() != '') ) { echo $domain->getWorkTimeEnd(); } else { echo '21:00'; ?><?php } ?>" />
                            </div>
                        </div>
                        
                        <br/><br/>

                        <div class="control-group">
                            <div class="controls">
                                <?php
                                if ($domain->getMode() == 'New'){ ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary"style="margin-left: 0px;">Save</button>
                                    <button type="button" class="btn" style="margin-left: 20px" onclick="javascript:clearForm();">Cancel</button>
                                <?php } else { ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;"> Update</button>
                                    <a class="btn" href="listDomains.php" style="margin-left: 20px">Cancel</a>
                               <?php } ?> 
                                
                            </div>
                        </div>
                    </div>
                 <?php } 
                  if ( $action['result'] == 'success' || $isUpdating ) { ?>
                    <div class="hero-unit" style="background-color: #FFFFFF; min-height: 50px; padding: 20px 0px">

                        <h4 style="margin: 0px;border-bottom: 2px solid #B3BFCA;">Saved Domain Details</h4>

                        <!-- <hr style="margin: 2px 0;"> -->
                        <br/>
                        <div style="margin-left: 0px">
                            <table class="table table-bordered" style="font-size: 14px;">
                                <tbody>
                                    <tr>
                                        <td class="table-heading">Name</td>
                                        <td><?php echo $domainDetails->getDomainName()?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Description</td>
                                        <td><?php echo $domainDetails->getDomainDesc()?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Working Days</td>
                                        <td><?php echo $domainDetails->getWorkDayStartFull() . " To " . $domainDetails->getWorkDayEndFull() ?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Working Hours</td>
                                        <td><?php echo $domainDetails->getWorkTimeStart() . " To " . $domainDetails->getWorkTimeEnd() ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        <?php 
                        if (!$isUpdating) { ?>
                            <div style="text-align: center">
                                <br/><br/>
                                <a class="btn btn-large btn-success" href="listDomains.php" >List Domains</a>
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
                document.getElementById("workDayStart").options[1].selected = true;
                document.getElementById("workDayEnd").options[5].selected = true;
                document.getElementById("workTimeStart").value = '09:00';
                document.getElementById("workTimeEnd").value = '21:00';
            }
        </script>

    </body>
</html>

<?php

function preventSQLInjectionAndValidate(Domain $domain){
    global $action, $text;
    
    $domainName = mysql_real_escape_string($_POST['name']);
    $description = mysql_real_escape_string($_POST['description']);
    $workDayStart = mysql_real_escape_string($_POST['workDayStart']);
    $workDayEnd = mysql_real_escape_string($_POST['workDayEnd']);
    $workTimeStart = mysql_real_escape_string($_POST['workTimeStart']);
    $workTimeEnd = mysql_real_escape_string($_POST['workTimeEnd']);
    
    if (empty($domainName)){ $action['result'] = 'error'; array_push($text,'Domain Name is required'); }
    
    if ($workDayStart >= $workDayEnd){
        $action['result'] = 'error'; array_push($text,'End Working Day should be later then the Start Working Day.');
    }
    if ($workTimeStart >= $workTimeEnd){
        $action['result'] = 'error'; array_push($text,'End Working Time should be later then the Start Working Time.');
    }
    
    //For Update
    $mode = mysql_real_escape_string($_POST['mode']);
    $domainid = mysql_real_escape_string($_POST['domainid']);
    if (!empty($domainid)){
        $domain->setDomainId($domainid);
    }
    
    
    $domain->setDomainName($domainName);
    $domain->setDomainDesc($description);
    $domain->setMode($mode);
    $domain->setWorkDayStart($workDayStart);
    $domain->setWorkDayEnd($workDayEnd);
    $domain->setWorkTimeStart($workTimeStart);
    $domain->setWorkTimeEnd($workTimeEnd);
    
}

?>
