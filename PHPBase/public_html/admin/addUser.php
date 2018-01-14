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
$user = new User();
$userDetails = NULL;
$allDomains = NULL;

$isUpdating = FALSE;
$mode = 'New';
$userMode = $user->getMode();

if (empty($userMode)){
    $user->setMode($mode);
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

    preventSQLInjectionAndValidate($user);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $saveFlag = $adminMgmtDAO->addUser($user);

        if ($saveFlag){

            //Fetch the user records
            $flag = $adminMgmtDAO->fetchUserDetails($user);
            if ($flag){
                $userDetails = $adminMgmtDAO->getUser();

                $action['result'] = 'success'; 
                if ($user->getMode() == 'New'){
                    array_push($text,'User record is added successfully.');
                }
                else{
                    array_push($text,'User record is updated successfully.');
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
    $userid = $_GET['userid'];
    $mode = 'update';
    $user->setMode($mode);
   
    if (!empty($userid)){
        $user->setUserId($userid);
        $flag = $adminMgmtDAO->fetchUserDetails($user);
        
        if ($flag){
            $userDetails = $adminMgmtDAO->getUser();
            $user = $userDetails;
            $user->setMode($mode);

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
        array_push($text,'User Id not received');
        $action['text'] = $text;
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
                    if ($user->getMode() == 'New'){ ?>
                        Add New User
                    <?php } else { ?>
                        Update Existing User
                    <?php } ?>
                  
              </h1>
                <p class="lead" style="font-size:17px">Add new User to allow 'view' access of the application</p>
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
                
            <form class="form-horizontal" id="addUserForm" name="addUserForm" method="post" action="">
                <input type="hidden" name="mode" id="mode" value="<?php echo $user->getMode();  ?>" />
                <input type="hidden" name="userid" id="userid" value="<?php echo $user->getUserId();  ?>" />
                
                <?php

                if ( $action['result'] != 'success' ||  $isUpdating ) { ?>
                    <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">

                        <legend style="border-bottom: 2px solid #e5e5e5;">Provide User Details</legend>
                        
                        <div class="control-group">
                            <label class="control-label" for="domain" style="font-weight: bold;">Domain to Associate with <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <select name="domain" id="domain" required style="width: 420px;margin-bottom: 0px;" >
                                    <option value="0" <?php if (!isset($user)){ ?> selected="selected" <?php } ?> >*** Select Domain ***</option>
                                    <?php 
                                    foreach ($allDomains as $domain){?>

                                    <option value="<?php echo $domain->getDomainId(); ?>" <?php if (isset($user) && ($user->getDomainId() == $domain->getDomainid())){ ?> selected="selected" <?php } ?> ><?php echo $domain->getDomainName(); ?></option>
                                   <?php }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label class="control-label" for="name" style="font-weight: bold;">Name <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="name" id="name" type="text" style="width: 400px;" value="<?php if (isset($user) && ($user->getName() != '') ) { echo $user->getName(); } ?>" placeholder="Name" required  />
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label class="control-label" for="username" style="font-weight: bold;">Login Username <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="username" id="username" type="text" style="width: 230px;" value="<?php if (isset($user) && ($user->getUsername() != '') ) { echo $user->getUsername(); } ?>" placeholder="UserName" required  />
                            </div>
                        </div>
                        
                        <?php 
                        if ($user->getMode() == 'New') { ?>
                        
                        <div class="control-group">
                            <label class="control-label" for="password" style="font-weight: bold;">Login Password <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                            <div class="controls">
                                <input name="password" id="password" type="password" style="width: 230px;" value="<?php if (isset($user) && ($user->getPassword() != '') ) { echo $user->getPassword(); } ?>" placeholder="Password" required  />
                            </div>
                        </div>
                        
                        <?php } ?>
                        
                        <div class="control-group">
                            <label class="control-label" for="email" style="font-weight: bold;">Email : </label>
                            <div class="controls">
                                <input name="email" id="email" type="email" style="width: 400px;" value="<?php if (isset($user) && ($user->getEmail() != '') ) { echo $user->getEmail(); } ?>" placeholder="email@gmail.com"  />
                            </div>
                        </div>

                        <br/><br/>

                        <div class="control-group">
                            <div class="controls">
                                <?php
                                if ($user->getMode() == 'New'){ ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Save</button>
                                    <button type="button" class="btn" style="margin-left: 20px" onclick="javascript:clearForm();">Cancel</button>
                                <?php } else { ?>
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Update</button>
                                    <a class="btn" href="listUsers.php" style="margin-left: 20px">Cancel</a>
                               <?php } ?> 
                                
                            </div>
                        </div>
                    </div>
                 <?php } 
                  if ( $action['result'] == 'success' || $isUpdating ) { ?>
                    <div class="hero-unit" style="background-color: #FFFFFF; min-height: 50px; padding: 20px 0px">

                        <h4 style="margin: 0px;border-bottom: 2px solid #B3BFCA;">Saved User Details</h4>

                        <!-- <hr style="margin: 2px 0;"> -->
                        <br/>
                        <div style="margin-left: 0px">
                            <table class="table table-bordered" style="font-size: 14px;">
                                <tbody>
                                    <tr>
                                        <td class="table-heading">Name</td>
                                        <td><?php echo $userDetails->getName()?></td>
                                    </tr>
                                    <tr>
                                        <td class="table-heading">Domain</td>
                                        <td><?php echo $userDetails->getDomainname()?></td>
                                    </tr>
                                    
                                        <tr>
                                            <td class="table-heading" style="background-color: #F20909;border-left: 3px solid #F20909;border-top: 3px solid #F20909;border-radius: 4px 0 0 0;" >Login Username</td>
                                            <?php 
                                            if (!$isUpdating){ ?>
                                                <td id="logintip" style="border-right: 3px solid #F20909;border-top: 3px solid #F20909;border-radius: 0 4px 0 0;" data-placement="top" title="These highlighted details should be shared with the user" ><?php echo $userDetails->getUsername()?></td>
                                            <?php } else { ?>
                                                <td style="border-right: 3px solid #F20909;border-top: 3px solid #F20909;border-radius: 0 4px 0 0;" ><?php echo $userDetails->getUsername()?></td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td class="table-heading" style="background-color: #F20909;border-left: 3px solid #F20909;border-bottom: 3px solid #F20909;border-radius: 0 0 0 4px;" >Login Password</td>
                                            <td style="border-right: 3px solid #F20909;border-bottom: 3px solid #F20909;border-radius: 0 0 4px 0;" ><?php echo $userDetails->getPassword()?></td>
                                        </tr>
                                    
                                    <tr>
                                        <td class="table-heading">Email</td>
                                        <td><?php echo $userDetails->getEmail()?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        <?php 
                        if (!$isUpdating) { ?>
                            <div style="text-align: center">
                                <br/><br/>
                                <a class="btn btn-large btn-success" href="listUsers.php" >List Users</a>
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
            $('#logintip').tooltip('toggle');
            
            function clearForm(){
                $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
                $(':checkbox, :radio').prop('checked', false);
            }
        </script>

    </body>
</html>

<?php

function preventSQLInjectionAndValidate(User $user){
    global $action, $text;
    $password = NULL;
    
    $domainid = mysql_real_escape_string($_POST['domain']);
    $name = mysql_real_escape_string($_POST['name']);
    $username = mysql_real_escape_string($_POST['username']);
    $email = mysql_real_escape_string($_POST['email']);
    
    if (isset($_POST['password'])){
        $password = mysql_real_escape_string($_POST['password']);
    }
    
    if (empty($domainid) || $domainid == "0"){ $action['result'] = 'error'; array_push($text,'Please select Domain'); }
    if (empty($name)){ $action['result'] = 'error'; array_push($text,'Name is required'); }
    if (empty($username)){ $action['result'] = 'error'; array_push($text,'Username is required'); }
    
    $mode = mysql_real_escape_string($_POST['mode']);
    if (empty($password) && $mode == 'New'){ $action['result'] = 'error'; array_push($text,'Password is required'); }
    
    //For Update
    $userid = mysql_real_escape_string($_POST['userid']);
    if (!empty($userid)){
        $user->setUserId($userid);
    }
    $user->setMode($mode);
    
    
    $user->setDomainId($domainid);
    $user->setName($name);
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setPassword($password);
    
}

?>
