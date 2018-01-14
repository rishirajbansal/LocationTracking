<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Project.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once '../functions.php';
include_once 'sessionMgmt.php';


date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 


$action = array();
$action['result'] = null;
$text = array();
$message = NULL;


$inputs = new AdminInputs();

$adminMgmtDAO = new AdminMgmtDAO();
$projectDetails = NULL;

$flag = $adminMgmtDAO->fetchAllProjectsWithMapDetails();

if (!$flag){
    $action['result'] = 'error'; 
    array_push($text,$adminMgmtDAO->getError());
    $action['text'] = $text;
}
else{
    $projectDetails = $adminMgmtDAO->getProjectDetails();
}
    
if (isset($_POST['update'])){
    preventSQLInjectionAndValidate($inputs);
    header("location: addProject.php?update=1&projectid=".$inputs->getProjectid());
}
else if (isset($_POST['delete'])){
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
    
        $deleteFlag = $adminMgmtDAO->deleteProject($inputs->getProjectid());

        if ($deleteFlag){
            $action['result'] = 'success'; 
            array_push($text,'Project record is removed successfully.');
            $action['text'] = $text;

            //Re-fetch the project records again
            $adminMgmtDAO->fetchAllProjectsWithMapDetails();
            $projectDetails = $adminMgmtDAO->getProjectDetails();
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
            @media (min-width: 1700px) { 
                .actions{
                    width: 11%;
                }
            }
            @media (min-width: 1500px) and (max-width: 1650px) { 
                .actions{
                    width: 15%;
                }
            }
            @media (min-width: 1400px) and (max-width: 1490px) { 
                .actions{
                    width: 19%;
                }
            }
            @media (min-width: 990px) and (max-width: 1390px) { 
                .actions{
                    width: 24%;
                }
            }
            @media (min-width: 768px) and (max-width: 979px) { 
                .actions{
                    width: 35%;
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
          <script src="../js/html5shiv.js"></script>
        <![endif]-->
        
        <script type="text/javascript">
        
            
        </script>
        
    </head>

    <body>
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">List Project Locations</h1>
              <p class="lead" style="font-size:17px">List of the existing projects with details</p>
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
            if (!empty($action['result']) && $action['result'] == 'success'){  ?>
                <div class="alert alert-success" style="margin-bottom: 0px;padding-bottom: 0px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo show_successMessages($action); ?>
                </div>
          <?php  }
          ?>
        
        <div class="container-fluid" style="padding-top: 30px;  min-height: 600px">
            <div class="row-fluid">
                
                <form class="form-inline" id="listProjectsForm" name="listProjectsForm" method="post" action="">
                    
                    <table class="table table-bordered table-striped" style="font-size: 14px;">
                        <thead>
                            <tr class="label-info" style="color: #FFFFFF">
                                <th style="text-align: center;" width="2%">#</th>
                                <th width="12%">Name</th>
                                <th width="12%">Associated Domain</th>
                                <th width="12%">Coordinates</th>
                                <th width="21%">Location</th>
                                <th class="actions">Actions</th>
                                <th>Map</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if (!empty($projectDetails)){
                                    $ctr = 1;
                                    $rowToggle=0;
                                    foreach ($projectDetails as $projectDetail) {
                                        $project = $projectDetail['project'];
                                        $filename = $projectDetail['filename'];
                                        ?>
                                        <tr <?php if ($rowToggle) { $rowToggle=0?>class="warning" <?php } else { ?> class="" <?php $rowToggle=1; } ?>>
                                            <td style="text-align: center;"><?php echo $ctr;?></td>
                                            <td><?php echo $project->getProjectname();?></td>
                                            <td><?php echo $project->getDomainname();?></td>
                                            <td><?php echo $project->getLatitude() . ' / ' . $project->getLongitude();?></td>
                                            <td><?php echo $project->getLocation();?></td>
                                            <td>
                                                &nbsp;<button name="update" value="<?php echo $project->getIdproject();?>" type="submit" class="btn btn-success" >Update</button>&nbsp;
                                                <button name="delete" value="<?php echo $project->getIdproject();?>" type="submit" class="btn btn-danger" >Delete</button>
                                            </td>
                                            <td><iframe align="center" style="  height: 300px;width: 600px;" src="mapMultipleProjectLocationMarkers.php?filename=<?php echo $filename; ?>&centerLatitude=<?php echo $project->getLatitude(); ?>&centerLongitude=<?php echo $project->getLongitude(); ?>&mode=list"  frameborder="yes" scrolling="yes"> </iframe></td>
                                        </tr>                  
                            <?php 
                                $ctr+=1 ; 
                                 }
                                }
                            ?>
                        </tbody>
                    </table>
                </form>
                
            </div>
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
    
    if (isset($_POST['delete'])) {
        $projectid = mysql_real_escape_string($_POST['delete']);
    }
    else{
        $projectid = mysql_real_escape_string($_POST['update']);
    }
    
    $inputs->setProjectid($projectid);
}

?>
