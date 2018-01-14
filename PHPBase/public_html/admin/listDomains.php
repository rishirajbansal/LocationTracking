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
$allDomains = NULL;
$inputs = new AdminInputs();

$flag = $adminMgmtDAO->fetchAllDomainDetails();

if (!$flag){
    $action['result'] = 'error'; 
    array_push($text,$adminMgmtDAO->getError());
    $action['text'] = $text;
}
else{
    $allDomains = $adminMgmtDAO->getAllDomains();
}

if (isset($_POST['update'])){
    preventSQLInjectionAndValidate($inputs);
    header("location: addDomain.php?update=1&domainid=".$inputs->getDomainid());
}
else if (isset($_POST['activate'])){
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
    
        $updateFlag = $adminMgmtDAO->activateInactivateDomain($inputs);

        if ($updateFlag){
            $action['result'] = 'success'; 

            if ($inputs->getDomainStatus() == 1){
                array_push($text,'Domain is <b>Deactivated</b> successfully.');
            }
            else{
                array_push($text,'Domain is <b>Activated</b> successfully.');
            }

            $action['text'] = $text;

            //Re-fetch the Domain records again
            $adminMgmtDAO->fetchAllDomainDetails();
            $allDomains = $adminMgmtDAO->getAllDomains();
        }
        else{
            $action['result'] = 'error'; 
            array_push($text,$adminMgmtDAO->getError());
            $action['text'] = $text;
        }
    }
}
else if (isset($_POST['delete'])){
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
    
        $deleteFlag = $adminMgmtDAO->deleteDomain($inputs);

        if ($deleteFlag){
            $action['result'] = 'success'; 
            array_push($text,'Domain record is removed completely & successfully.');
            $action['text'] = $text;

            //Re-fetch the Domain records again
            $adminMgmtDAO->fetchAllDomainDetails();
            $allDomains = $adminMgmtDAO->getAllDomains();
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
            @media (min-width: 1700px) { 
                .actions{
                    width: 22%;
                }
                .vis{
                    width: 8%;
                }
            }
            @media (min-width: 1500px) and (max-width: 1600px) { 
                .actions{
                    width: 26%;
                }
            }
            @media (min-width: 1300px) and (max-width: 1490px) { 
                .actions{
                    width: 29%;
                }
            }
            @media (min-width: 990px) and (max-width: 1290px) { 
                .actions{
                    width: 65%;
                }
            }
            @media (min-width: 768px) and (max-width: 979px) { 
                .actions{
                    width: 75%;
                }
            }
            .table-heading {
                background-color: #84BBD6;
                color: #FFFFFF;
                font-weight: bold;
                width: 35%
            }
            .table tbody tr.warning > td {
                background-color: #FFFFFF !important;
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
        
        <script>
            function getDomainId(domainid){
                document.getElementById("domainSelected").value = domainid;
            }
        </script>
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">List of Domains</h1>
              <p class="lead" style="font-size:17px">List of the existing domain with details</p>
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
                
            <form class="form-inline" id="listDomainForm" name="listDomainForm" method="post" action="">
                <input type="hidden" name="domainSelected" id="domainSelected" value="" />
                
                <table class="table table-bordered table-striped" style="font-size: 14px;">
                    <thead>
                        <tr class="label-info" style="color: #FFFFFF">
                            <th style="text-align: center;" width="2%">#</th>
                            <th width="18%">Name</th>
                            <th width="32%">Description</th>
                            <th class="vis">Visibility</th>
                            <th width="12%">Creation Date</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($allDomains)){
                                $ctr = 1;
                                $rowToggle=0;
                                foreach ($allDomains as $domain) {
                                    ?>
                                    <tr <?php if ($rowToggle) { $rowToggle=0?>class="warning" <?php } else { ?> class="" <?php $rowToggle=1; } ?>>
                                        <td style="text-align: center;"><?php echo $ctr;?></td>
                                        <td><?php echo $domain->getDomainName();?></td>
                                        <td><?php echo $domain->getDomainDesc();?></td>
                                        <td><?php if ($domain->getStatus() == 1) { ?> <img src="../img/active.png" class="img-rounded" width="20%">  <?php } else { ?> <img src="../img/inactive.png" class="img-rounded" width="20%"> <?php } echo $domain->getVisibility();?></td>
                                        <td><?php echo $domain->getFormattedCreationDate();?></td>
                                        <td>
                                            <?php $info1 = "Working Days : " . $domain->getWorkDayStartFull() . " To " . $domain->getWorkDayEndFull(); ?>
                                            <?php $info2 = " Working Hours : " . $domain->getWorkTimeStart() . " To " . $domain->getWorkTimeEnd(); ?>
                                            <a href="#" class="btn btn-info" data-toggle="popover" data-placement="top" data-content="<?php echo $info1; echo $info2;?>" title="More Information">More Info</a>
                                            &nbsp;&nbsp;<button name="update" value="<?php echo $domain->getDomainId();?>" type="submit" class="btn btn-success" >Update</button>&nbsp;&nbsp;
                                            <button name="activate" value="<?php echo $domain->getDomainId() . '|' . $domain->getStatus();?>" type="submit" class="btn <?php if ($domain->getStatus() == 0) { ?> btn-inverse" <?php } else { ?> " <?php } ?> ><?php if ($domain->getStatus() == 1) { ?> Deactivate <?php } else { ?> &nbsp;&nbsp;Activate&nbsp;&nbsp;&nbsp; <?php }?></button>&nbsp;&nbsp;
                                            
                                            <button name="deleteAlertBtn" value="<?php echo $domain->getDomainId();?>" class="btn btn-danger" type="button" data-toggle="modal" data-target="#deleteAlert" onclick="javascript:getDomainId(<?php echo $domain->getDomainId();?>);">Delete</button>
                                        </td>
                                    </tr>
                                    
                                    <div id="deleteAlert" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteLabel" aria-hidden="true" style="width: 600px;">
                                        <div class="modal-header" style="background-color: #DF524D;color: #FFF;border-radius: 5px 5px 0px 0px;">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #000000;">×</button>
                                            <h3 id="deleteLabel">Delete Confirmation</h3>
                                        </div>
                                        <div class="modal-body">
                                            <div class="jumboHeading">
                                                <p class="lead" ><h3 style="color: #FF0000">Are you sure want to REMOVE this domain ?</h3></p>
                                                <p style="font-size: 16px" >This process will have following implications: </p>
                                                <ul>
                                                    <li>All Workers associated with this domain will be removed.</li>
                                                    <li>All Projects associated with this domain will be dropped.</li>
                                                    <li>Location histories for all associated workers will be vanished.</li>
                                                    <li>Users who are linked with this domain will no more able to access the application and could not login as they would also get removed.</li>
                                                </ul>
                                                <br/>
                                                <p style="font-size: 16px" >Be cautious, this process cannot be undone. This is the final confirmation.</p>
                                                <br/>
                                                <p style="font-size: 16px;font-style: italic;color: #1886AA" >If you opt for 'Deletion', Remember to update the associated Users of this domain with other available domain after deletion.</p>
                                                <br/><br/>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
                                            <button name="delete" value="<?php echo $domain->getDomainId();?>" class="btn btn-danger btn-large" type="submit" >Confirm</button>
                                        </div>
                                    </div>
                        <?php 
                            $ctr+=1 ; 
                             }
                            }
                        ?>
                    </tbody>
                </table>

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
            $('#info').popover('toggle');
        </script>

    </body>
</html>

<?php

function preventSQLInjectionAndValidate(AdminInputs $inputs){
    global $action, $text;
    
    $domainid = NULL;
    $domainStatus = NULL;
    
    if (isset($_POST['update'])) {
        $domainid = mysql_real_escape_string($_POST['update']);
    }
    else  if (isset($_POST['activate'])) {
        $temp = mysql_real_escape_string($_POST['activate']);
        $temp = explode('|', $temp);
        $domainid = $temp[0];
        $domainStatus = $temp[1];
    }
    else{
        $domainid = mysql_real_escape_string($_POST['domainSelected']);
    }
    
    $inputs->setDomainId($domainid);
    $inputs->setDomainStatus($domainStatus);
    
}

?>
