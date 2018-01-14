<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
//include_once 'sessionMgmt.php';

if (session_id() == "") 
    session_start();

if (!empty($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] == 1) && !empty($_SESSION['user'])) {
    
    unset($_SESSION['loggedIn']);
    unset($_SESSION['begin']);
    unset($_SESSION['user']);
    
    session_destroy();

}
else{
    header("location: login.php");
}

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Location History &middot; Logout</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-responsive.css" rel="stylesheet">
        <link href="css/docs.css" rel="stylesheet">
        <link href="js/google-code-prettify/prettify.css" rel="stylesheet">
        
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
                max-width: 780px;
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
          <script src="js/html5shiv.js"></script>
        <![endif]-->
        
        
    </head>

    <body >
      
        <!-- Include top bar page -->
        <?php include("topbar.php"); ?>
        
        <div class="container" style="padding-top: 30px; min-height: 600px ">
                
            <form class="form-horizontal" id="logoutForm" name="logoutForm" method="post" action="">
                
                <br/><br/><br/><br/><br/><br/><br/><br/><br/>
                <div class="alert alert-success" style="text-align: center;">
                    
                    <?php
                    if (isset($_GET['sessionTimeout'])){ ?>
                    <p class="lead"><h3>You have been logged out from the application <br/> due to the inactivity for more then <p class="text-error"><?php echo Config::$sessionTimeout; ?> minutes.</p></h3></p>
    
                    <?php } else { ?>
                        <p class="lead"><h3>You have been logged out from the application successfully.</h3></p>
                        <br/><br/>
                        <p style="font-size: 24px;color: #1886AA;text-shadow: 2px 2px #FFFFFF;" >Have a nice day !</p>
                    <?php } ?>
                        
                   <br/>
                </div>
                <br/><br/><br/><br/>
                <div style="text-align: center">
                    <a class="btn btn-success btn-large" href="login.php" >Login again </a>
                </div>
                <br/><br/><br/><br/><br/><br/>
            </form>
        </div>
        
      
        <!-- Include bottom bar page -->
        <?php include("bottombar.php"); ?>


        <script src="js/jquery.js"></script>
        <script src="js/bootstrap-transition.js"></script>
        <script src="js/bootstrap-alert.js"></script>
        <script src="js/bootstrap-modal.js"></script>
        <script src="js/bootstrap-dropdown.js"></script>
        <script src="js/bootstrap-scrollspy.js"></script>
        <script src="js/bootstrap-tab.js"></script>
        <script src="js/bootstrap-tooltip.js"></script>
        <script src="js/bootstrap-popover.js"></script>
        <script src="js/bootstrap-button.js"></script>
        <script src="js/bootstrap-collapse.js"></script>
        <script src="js/bootstrap-carousel.js"></script>
        <script src="js/bootstrap-typeahead.js"></script>
        <script src="js/bootstrap-affix.js"></script>

        <script src="js/holder/holder.js"></script>
        <script src="js/google-code-prettify/prettify.js"></script>

        <script src="js/application.js"></script>

    </body>
</html>
