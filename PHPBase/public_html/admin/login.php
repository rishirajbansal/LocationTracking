<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminInputs.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'AdminMgmtDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once '../functions.php';

$action = array();
$action['result'] = null;
$text = array();
$message = NULL;

date_default_timezone_set(Config::$timezone);
ini_set('max_execution_time', 300); 

$adminMgmtDAO = new AdminMgmtDAO();
$inputs = new AdminInputs();

    
if (isset($_POST['submit'])){
    
    if (session_id() == "") {
        session_start();
        session_destroy();
    }

    preventSQLInjectionAndValidate($inputs);
    $action['text'] = $text;
    
    if ($action['result'] != 'error'){
        
        $isValid = $adminMgmtDAO->adminLogin($inputs);

        if ($isValid){
            session_start();
            $_SESSION['loggedIn'] = 1;
            $_SESSION['isAdmin'] = 1;
            $_SESSION['begin'] = time();
                
            $action['result'] = 'success';
            
            header("location: home.php");
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
        <title>Admin Panel &middot; Login</title>
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
                max-width: 630px;
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
              <h1 style="font-size:27px">Super User Login</h1>
              <p class="lead" style="font-size:17px">Login verification process to access Admin Panel</p>
           </div>
        </header>
        
        <div class="container" style="padding-top: 30px; min-height: 600px ">
                
            <form class="form-horizontal" id="loginForm" name="loginForm" method="post" action="">
                
                <?php

                if ( $action['result'] == 'error' ) { ?>
                    <br/><br/><br/><br/><br/>
                    <div class="alert alert-error" style="text-align: center;">
                        <br/>

                        <p class="lead"><h3 style="color: #FF0000;">Login credentials are not valid.</h3></p>

                      <br/><br/>
                       <p style="font-size: 18px;color: #1886AA;text-shadow: 2px 2px #FFFFFF;" >Please provide valid login credentials for successful login.</p>
                       <br/><br/>
                    </div>
                    <br/><br/><br/><br/>
                    <div style="text-align: center">
                        <a class="btn btn-success btn-large" href="login.php" >Try Login again</a>
                    </div>
                    
                
                <?php } else { ?>
                    <br/><br/><br/><br/><br/>
                    <div class="well sidebar-nav" style="min-height: 200px;margin-left: 0px;">

                            <legend style="border-bottom: 2px solid #e5e5e5;">Provide Login Credentials</legend>

                            <div class="control-group">
                                <label class="control-label" for="name" style="font-weight: bold;">Username <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <input name="username" id="username" type="text" style="width: 300px;border-radius: 4px 0 0 4px;" value="" placeholder="Username" required  />
                                        <span class="add-on" style="border-radius: 0 4px 4px 0;height: 26px;background-color: #0483C2;border-color: #0483C2;">
                                            <i class="icon-user icon-white" style="font-size: 10px;width: 18px;margin-top: 5px;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label" for="name" style="font-weight: bold;">Password <span style="color: rgb(240, 23, 23);font-size: 16px;">*</span> : </label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <input name="password" id="password" type="password" style="width: 300px;border-radius: 4px 0 0 4px;" value="" placeholder="Password" required  />
                                        <span class="add-on" style="border-radius: 0 4px 4px 0;height: 26px;background-color: #0483C2;border-color: #0483C2;">
                                            <i class="icon-lock icon-white" style="font-size: 10px;width: 18px;margin-top: 5px;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <br/><br/>

                            <div class="control-group">
                                <div class="controls">
                                    <button name="submit" value="submit" type="submit" class="btn btn-primary btn-large" style="margin-left: 0px;">Login</button>
                                    <button type="reset" class="btn btn-large" style="margin-left: 20px">Reset</button>
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
    
    $username = mysql_real_escape_string($_POST['username']);
    $password = mysql_real_escape_string($_POST['password']);
    
    if (empty($username)){ $action['result'] = 'error'; array_push($text,'Please enter username'); }
    if (empty($password)){ $action['result'] = 'error'; array_push($text,'Please enter password'); }
    
    $inputs->setLogin_username($username);
    $inputs->setLogin_password($password);
    
}

?>
