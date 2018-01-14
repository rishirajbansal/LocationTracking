<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'User.php';
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
$allUsers = NULL;
$inputs = new AdminInputs();

$flag = $adminMgmtDAO->fetchAllUserDetails();

if (!$flag){
    $action['result'] = 'error'; 
    array_push($text,$adminMgmtDAO->getError());
    $action['text'] = $text;
}
else{
    $allUsers = $adminMgmtDAO->getAllUsers();
}

if (isset($_POST['update'])){
    preventSQLInjectionAndValidate($inputs);
    header("location: addUser.php?update=1&userid=".$inputs->getUserid());
}
else if (isset($_POST['chgPwd'])){
    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
    
        $updateFlag = $adminMgmtDAO->changeUserPassword($inputs);

        if ($updateFlag){
            $action['result'] = 'success';
            array_push($text,'User Password is <b>changed</b> successfully.');
            $action['text'] = $text;

            //Re-fetch the User records again
            $adminMgmtDAO->fetchAllUserDetails();
            $allUsers = $adminMgmtDAO->getAllUsers();
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
    
        $deleteFlag = $adminMgmtDAO->deleteUser($inputs);

        if ($deleteFlag){
            $action['result'] = 'success'; 
            array_push($text,'User record is removed completely & successfully.');
            $action['text'] = $text;

            //Re-fetch the User records again
            $adminMgmtDAO->fetchAllUserDetails();
            $allUsers = $adminMgmtDAO->getAllUsers();
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
        <title>Admin Panel &middot; User Management</title>
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
                    width: 19%;
                }
            }
            @media (min-width: 1500px) and (max-width: 1600px) { 
                .actions{
                    width: 23%;
                }
            }
            @media (min-width: 1200px) and (max-width: 1490px) { 
                .actions{
                    width: 29%;
                }
            }
            @media (min-width: 990px) and (max-width: 1150px) { 
                .actions{
                    width: 35%;
                }
            }
            @media (min-width: 768px) and (max-width: 979px) { 
                .actions{
                    width: 35%;
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
            function getUserId(userid){
                document.getElementById("userSelected").value = userid;
            }
            
            function clearData(){
                document.getElementById("newpassword").value = '';
            }
            
            function modalSubmit(){
                document.getElementById("newPasswordModal").value = document.getElementById("newpassword").value
            }
        </script>
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
      
        <header class="jumbotron subhead" id="overview">
            <div class="container" style="padding-left: 35px;margin-left: inherit"> 
              <h1 style="font-size:27px">List of Users</h1>
              <p class="lead" style="font-size:17px">List of the existing users with details</p>
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
                
            <form class="form-inline" id="listUserForm" name="listUserForm" method="post" action="">
                <input type="hidden" name="userSelected" id="userSelected" value="" />
                <input type="hidden" name="newPasswordModal" id="newPasswordModal" value="" />
                
                <table class="table table-bordered table-striped" style="font-size: 14px;">
                    <thead>
                        <tr class="label-info" style="color: #FFFFFF">
                            <th style="text-align: center;" width="2%">#</th>
                            <th width="15%">Name</th>
                            <th width="16%">Associated Domain</th>
                            <!-- <th width="12%">Login Username</th> -->
                            <th >Login Username</th>
                            <th width="17%">Login Password</th>
                            <th width="19%">Email</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($allUsers)){
                                $ctr = 1;
                                $rowToggle=0;
                                foreach ($allUsers as $user) {
                                    ?>
                                    <tr <?php if ($rowToggle) { $rowToggle=0?>class="warning" <?php } else { ?> class="" <?php $rowToggle=1; } ?>>
                                        <td style="text-align: center;"><?php echo $ctr;?></td>
                                        <td><?php echo $user->getName();?></td>
                                        <td><?php echo $user->getDomainname();?></td>
                                        <td><?php echo $user->getUsername();?></td>
                                        <td><?php echo $user->getPassword();?></td>
                                        <td><?php echo $user->getEmail();?></td>
                                        <td>
                                            &nbsp;&nbsp;<button name="update" value="<?php echo $user->getUserId();?>" type="submit" class="btn btn-success" >Update</button>&nbsp;
                                            <button name="chgPwdBtn" value="<?php echo $user->getUserId();?>" type="button" class="btn btn-warning" data-toggle="modal" data-target="#changePwdModal" onclick="javascript:getUserId(<?php echo $user->getUserId();?>);">Change Password</button>&nbsp;
                                            <button name="deleteAlertBtn" value="<?php echo $user->getUserId();?>" class="btn btn-danger" type="button" data-toggle="modal" data-target="#deleteAlert" onclick="javascript:getUserId(<?php echo $user->getUserId();?>);">Delete</button>
                                        </td>
                                    </tr>
                                    
                                    <div id="deleteAlert" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteLabel" aria-hidden="true" style="width: 600px;">
                                        <div class="modal-header" style="background-color: #DF524D;color: #FFF;border-radius: 5px 5px 0px 0px;">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #000000;">×</button>
                                            <h3 id="deleteLabel">Delete Confirmation</h3>
                                        </div>
                                        <div class="modal-body">
                                            <div class="jumboHeading">
                                                <p class="lead" ><h3 style="color: #FF0000">Are you sure want to REMOVE this user ?</h3></p>
                                                <p style="font-size: 16px" >Be cautious, this process cannot be undone. This is the final confirmation.</p>
                                                <br/><br/>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
                                            <button name="delete" value="<?php echo $user->getUserId();?>" class="btn btn-danger btn-large" type="submit" >Confirm</button>
                                        </div>
                                    </div>
                    
                                    <div id="changePwdModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="changePwdLabel" aria-hidden="true" style="width: 600px;">
                                        <div class="modal-header" style="background-color: #087C96;color: #FFF;border-radius: 5px 5px 0px 0px;">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #000000;">×</button>
                                            <h3 id="changePwdLabel">Change Password</h3>
                                        </div>
                                        <div class="modal-body">
                                            <br/><br/>
                                            <label for="newpassword" style="font-weight: bold;margin-right: 10px;margin-left: 10px;">New Password : </label>
                                            <input name="newpassword" id="newpassword" type="password" style="width: 300px;" placeholder="New Password"  />
                                            <br/><br/><br/>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-large" data-dismiss="modal" aria-hidden="true" onclick="javascript:clearData();">Cancel</button>
                                            <button name="chgPwd" value="<?php echo $user->getUserId();?>" class="btn btn-primary btn-large" type="submit" onclick="javascript:modalSubmit();">Update</button>
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

    </body>
</html>

<?php

function preventSQLInjectionAndValidate(AdminInputs $inputs){
    global $action, $text;
    
    $userid = NULL;
    
    if (isset($_POST['update'])) {
        $userid = mysql_real_escape_string($_POST['update']);
    }
    else  if (isset($_POST['chgPwd'])) {
        $userid = mysql_real_escape_string($_POST['userSelected']);
        $password = mysql_real_escape_string($_POST['newPasswordModal']);
        if (empty($password)){ $action['result'] = 'error'; array_push($text,'Please provide New Password'); }
        
        $inputs->setNewPassword($password);
    }
    else{
        $userid = mysql_real_escape_string($_POST['userSelected']);
    }
    
    $inputs->setUserid($userid);
    
}

?>
