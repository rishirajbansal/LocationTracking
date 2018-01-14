<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
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
$inputs = new AdminInputs();

    
if (isset($_POST['submit'])){

    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $saveFlag = $adminMgmtDAO->changeSuperUserPassword($inputs);

        if ($saveFlag){
            $action['result'] = 'success';
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
        <title>Admin Panel &middot; Settings</title>
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
                max-width: 760px;
              }
              .form-horizontal input[type="text"],
              .form-horizontal input[type="password"] {
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
              <h1 style="font-size:27px">Change Super User Password</h1>
              <p class="lead" style="font-size:17px">Replace Super User Password with new</p>
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
        
        <div class="container" style="padding-top: 30px; min-height: 600px ">
                
            <form class="form-horizontal" id="sChangePwdForm" name="sChangePwdForm" method="post" action="">
                
                <?php

                if ( $action['result'] == 'success' ) { ?>
                
                    <div class="alert alert-info" style="text-align: center;">

                        <p class="lead"><h3 style="color: #1886AA;text-shadow: 2px 2px #FFFFFF;">Super User Password has been updated successfully.</h3></p>

                      <br/><br/>
                       <p style="font-size: 18px;color: #FF0000;" >Please use new password for login next time.</p>
                       <br/>
                    </div>
                    <br/><br/>
                    <div style="text-align: center">
                        <a class="btn btn-success btn-large" href="home.php" >Back to Home</a>
                    </div>
                    
                
                <?php } else { ?>
                    <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">

                            <legend style="border-bottom: 2px solid #e5e5e5;">Update Password</legend>

                            <div class="control-group">
                                <label class="control-label" for="name" style="font-weight: bold;">Old Password <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                                <div class="controls">
                                    <input name="oldpassword" id="oldpassword" type="password" style="width: 400px;" value="" placeholder="Password" required  />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="name" style="font-weight: bold;">New Password <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                                <div class="controls">
                                    <input name="newpassword" id="newpassword" type="password" style="width: 400px;" value="" placeholder="Password" required  />
                                </div>
                            </div>

                            <br/><br/>

                            <div class="control-group">
                                <div class="controls">
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary" style="margin-left: 0px;">Save</button>
                                    <button type="reset" class="btn" style="margin-left: 20px">Cancel</button>
                                </div>
                            </div>
                        </div>
                
                <?php } ?>
                 
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
    
    $oldpassword = mysql_real_escape_string($_POST['oldpassword']);
    $newpassword = mysql_real_escape_string($_POST['newpassword']);
    
    if (empty($oldpassword)){ $action['result'] = 'error'; array_push($text,'Please enter Old password'); }
    if (empty($newpassword)){ $action['result'] = 'error'; array_push($text,'Please enter New password'); }
    
    $inputs->setS_oldPassword($oldpassword);
    $inputs->setS_newPassword($newpassword);
    
}

?>
