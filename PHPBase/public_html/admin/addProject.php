<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'LocationHistoryDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Project.php';
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
$projectDetails = NULL;
$project = new Project();

$allDomains = NULL;
$mode = 'New';
$projectMode = $project->getMode();
$isUpdating = FALSE;

if (empty($projectMode)){
    $project->setMode($mode);
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

    preventSQLInjectionAndValidate($project);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $saveFlag = $adminMgmtDAO->addProject($project);
        
        if ($saveFlag){

            //Fetch the project records
            $flag = $adminMgmtDAO->fetchProjectWithMapDetails($project);
            if ($flag){
                $projectDetails = $adminMgmtDAO->getProjectDetails();

                $action['result'] = 'success'; 
                if ($project->getMode() == 'New'){
                    array_push($text,'Project record is added successfully.');
                }
                else{
                    array_push($text,'Project record is updated successfully.');
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
    $projectid = $_GET['projectid'];
    $mode = 'update';
    $project->setMode($mode);
    
    if (!empty($projectid)){
        $project->setIdproject($projectid);
        $flag = $adminMgmtDAO->fetchProjectWithMapDetails($project);
        
        if ($flag){
            $projectDetails = $adminMgmtDAO->getProjectDetails();
            $record = $projectDetails[0];
            $project = $record['project'];
            $project->setMode($mode);

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
        array_push($text,'Project Id not received');
        $action['text'] = $text;
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Admin Panel &middot; Projects Management</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
        
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
             .form-horizontal {
                margin: 0px auto 20px;
                max-width: 1200px;
              }
              .form-horizontal input[type="text"],
              .form-horizontal textarea,
              .form-horizontal select{
                font-size: 15px;
                height: auto;
                //margin-bottom: 15px;
                padding: 7px 9px;
              }
              .form-horizontal label{
                font-size: 15px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
                width: 280px !important;
              }
              .form-horizontal .controls {
                margin-left: 300px;
              }
        </style>

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="../js/html5shiv.js"></script>
        <![endif]-->
        
        <script type="text/javascript">
        
            
        </script>
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">
                  <?php
                    if ($project->getMode() == 'New'){ ?>
                        Add New Project Location
                    <?php } else { ?>
                        Update Existing Project Location
                    <?php } ?>
                  
              </h1>
              <p class="lead" style="font-size:17px">Create Personalized points on the map to depict the project location</p>
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
            
            <form class="form-horizontal" id="addProject" name="addProject" method="post" action="">
                <input type="hidden" name="mode" id="mode" value="<?php echo $project->getMode();  ?>" />
                <input type="hidden" name="projectid" id="projectid" value="<?php echo $project->getIdproject();  ?>" />
                
                <?php

                if ( $action['result'] != 'success' ||  $isUpdating ) { ?>

                    <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">
                        <legend style="border-bottom: 2px solid #e5e5e5;">Provide Project Details</legend>
                        
                        <div class="control-group">
                            <label class="control-label" for="domain" style="font-weight: bold;">Domain to Associate with <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <select name="domain" id="domain" required style="width: 350px;">
                                    <option value="0" <?php if (!isset($project)){ ?> selected="selected" <?php } ?> >*** Select Domain ***</option>
                                    <?php 
                                    foreach ($allDomains as $domain1){?>

                                    <option value="<?php echo $domain1->getDomainId(); ?>" <?php if (isset($project) && ($project->getDomainId() == $domain1->getDomainid())){ ?> selected="selected" <?php } ?> ><?php echo $domain1->getDomainName(); ?></option>
                                   <?php }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label class="control-label" for="name" style="font-weight: bold;">Project Name <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="name" id="name" type="text" style="width: 400px;" value="<?php if (isset($project) && ($project->getProjectname() != '') ) { echo $project->getProjectname(); } ?>" placeholder="Project Name" required />
                            </div>
                        </div>
                        
                        <div style="background-color: #E7E7E7;border-radius: 4px;padding-top: 5px;">
                            <div class="control-group">
                                <br/>
                                <label class="control-label" for="" style="font-weight: bold;">Project Location <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                                <div class="controls">
                                    <input name="latitude" id="latitude" type="text" value="<?php if (isset($project) && ($project->getLatitude() != '') ) { echo $project->getLatitude(); } ?>" placeholder="39.5834896" style="max-width: 175px;" pattern="[-]?[0-9]*[.]?[0-9]*" title="Coordinates Format"/>
                                    &nbsp;<font size="5px">/</font>&nbsp;
                                    <input name="longitude" id="longitude" type="text" value="<?php if (isset($project) && ($project->getLongitude() != '') ) { echo $project->getLongitude(); } ?>" placeholder="2.647442" style="max-width: 175px;" pattern="[-]?[0-9]*[.]?[0-9]*" title="Coordinates Format"/>
                                    <span class="help-block">Coordinates (Latitude / Longitude)</span>
                                    <br/>
                                    <font size="3px">OR</font>
                                    <br/><br/>
                                    <input name="address" id="address" type="text" style="width: 700px;" value="<?php if (isset($project) && ($project->getLocation() != '') ) { echo $project->getLocation(); } ?>" placeholder="Illes Balears, Spain" />
                                    <span class="help-block">Address (More precise the address, More accurate the position)</span>
                                </div>
                                <br/>
                            </div>
                        </div>
                        
                        <br/>
                        
                        <div class="control-group">
                            <div class="controls">
                                <?php
                                if ($project->getMode() == 'New'){ ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Save</button>
                                    <button type="button" class="btn" style="margin-left: 20px" onclick="javascript:clearForm();">Cancel</button>
                                <?php } else { ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Update</button>
                                    <a class="btn" href="listProjects.php" style="margin-left: 20px">Cancel</a>
                               <?php } ?> 
                                
                            </div>
                        </div>

                    </div>
                
                <?php } 
                  if ( $action['result'] == 'success' || $isUpdating ) { ?>

                    <div>
                        <div class="hero-unit" style="background-color: #FFFFFF; min-height: 400px; padding: 20px 0px">

                            <?php

                             if ($action['result'] == 'success') { ?>
                            
                                <h4 style="margin: 0px;border-bottom: 2px solid #B3BFCA;">Saved Project Details</h4>

                                <!-- <hr style="margin: 2px 0;"> -->
                                <br/>
                                <div style="margin-left: 0px">
                                    <table class="table table-bordered table-striped" style="font-size: 14px;">
                                        <thead>
                                            <tr class="label-info" style="color: #FFFFFF">
                                                <th width="25%">Name</th>
                                                <th width="25%">Domain</th>
                                                <th width="15%">Coordinates</th>
                                                <th>Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $record = $projectDetails[0];
                                            $project = $record['project']; ?>
                                            <tr>
                                                <td><?php echo $project->getProjectname();?></td>
                                                <td><?php echo $project->getDomainname();?></td>
                                                <td><?php echo $project->getLatitude() . ' / ' . $project->getLongitude();?></td>
                                                <td><?php echo $project->getLocation();?></td>

                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div style="margin-left: 0px">
                                    <?php
                                          $filename = $record['filename'];
                                    ?>
                                    <iframe align="center" style="height: 650px;width: 1200px;" src="mapMultipleProjectLocationMarkers.php?filename=<?php echo $filename; ?>&centerLatitude=<?php echo $project->getLatitude(); ?>&centerLongitude=<?php echo $project->getLongitude(); ?>&mode=new"  frameborder="yes" scrolling="yes"> </iframe>
                                </div>

                            <?php } ?>
                                
                            <?php 
                            if (!$isUpdating) { ?>
                                <div style="text-align: center">
                                    <br/><br/>
                                    <a class="btn btn-large btn-success" href="listProjects.php" >List Projects</a>
                                </div>
                            <?php } ?>

                        </div>
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

function preventSQLInjectionAndValidate(Project $project){
    global $action, $text;
    
    $projectname = mysql_real_escape_string($_POST['name']);
    $latitude = mysql_real_escape_string($_POST['latitude']);
    $longitude = mysql_real_escape_string($_POST['longitude']);
    $address = mysql_real_escape_string($_POST['address']);
    $mode = mysql_real_escape_string($_POST['mode']);
    $domainid = mysql_real_escape_string($_POST['domain']);
    $projectid = mysql_real_escape_string($_POST['projectid']);
    
    if (empty($domainid) || $domainid == "0"){ $action['result'] = 'error'; array_push($text,'Please select Domain'); }
    if (empty($projectname)){ $action['result'] = 'error'; array_push($text,'Project Name is required'); }
    if (empty($latitude) && empty($longitude) && empty($address)){ $action['result'] = 'error'; array_push($text,'Please provide either Project address or Project coordinates'); }
    if (empty($address)){
        if (empty($latitude) && !empty($longitude)){
            $action['result'] = 'error'; array_push($text,'Please provide both coordinates, it seems that <b>Latitude</b> coordinates are not provided.');
        }
        else if (!empty($latitude) && empty($longitude)){
            $action['result'] = 'error'; array_push($text,'Please provide both coordinates, it seems that <b>Longitude</b> coordinates are not provided.');
        }
        else{
            $project->setLatitude($latitude);
            $project->setLongitude($longitude);
        }
    }
    else{
        $project->setLocation($address);
    }
    
    $project->setProjectname($projectname);
    $project->setLatitude($latitude);
    $project->setLongitude($longitude);
    $project->setMode($mode);
    $project->setDomainId($domainid);
    
    //For update
    if (!empty($projectid)){
        $project->setIdproject($projectid);
    }
    
}

?>
