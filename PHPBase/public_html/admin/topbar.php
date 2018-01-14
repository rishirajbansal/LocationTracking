<head>
    <style>
        .navbar-inverse .nav li.dropdown.open{
            background-color: #111111;
            background-image: linear-gradient(to bottom, #111111, #111111) !important ;
        }
        .navbar-inverse .nav li.dropdown > .dropdown-toggle .caret {
            border-top-color: #FFFFFF;
            border-bottom-color: #FFFFFF;
          }
          .navbar .nav > li {
              border-left: 1px solid #FFFFFF;
              padding: 0px 20px;
          }
          
    </style>
</head>

<div class="navbar navbar-inverse navbar-fixed-top" >
    <div class="navbar-inner" style="background-color: #0D3855;background-image: -moz-linear-gradient(top, #0D3855, #0294DC); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0D3855), to(#0294DC)); background-image: -webkit-linear-gradient(top, #FFFFFF, #679AB0);background-image: -o-linear-gradient(top, #0D3855, #0294DC); background-image: linear-gradient(to bottom, #0D3855, #0294DC);">
        <div class="container-fluid">
            
            <a class="brand" href="home.php" style="float: left;padding-right: 80px;font-size: 20px;color:#FFFFFF;//padding-top: 0px;padding-bottom: 0px;">
                <!--<font style="font-size: 12px;float: left;color:#FFFFFF">Location History Tracking</font><br>-->
                Administration Panel
            </a>
            
            <?php 
            if (!empty($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] == 1) && !empty($_SESSION['isAdmin']) && ($_SESSION['isAdmin'] == 1)) { ?>
            
                <ul class="nav" role="navigation" style="font-size: 14px;">
                    <li class="">
                        <a href="home.php" style="color: #FFFFFF;">Home</a>
                    </li>
                    <!--<li class="dropdown">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Monetarization <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="monitorAll.php">Monitor All</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="monitorProject.php">Project Based Monitoring</a></li>
                        </ul>
                    </li>-->
                    <li class="dropdown">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Domains <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="addDomain.php">Add New Domain</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="listDomains.php">List Domains</a></li>
                        </ul>
                    </li>
                    <li class="dropdown" >
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Users <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="addUser.php">Add New User</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="listUsers.php">List Users</a></li>
                         </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Project Locations <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="addProject.php">Add New Project Location</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="listProjects.php">List Projects Location</a></li>
                         </ul>
                    </li>
                    <li class="dropdown" style="border-right: 1px solid #FFFFFF; ">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Workers <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="addWorker.php">Add New Worker</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="listWorkers.php">List Workers</a></li>
                         </ul>
                    </li>
                    <li class="dropdown" style="border-left: none;    background-image: linear-gradient(to bottom, #B5310F, #FFAF13);border-right: 1px solid #FFFFFF;">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">System Management <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="retreiveLocation.php">Retrieve Location History</a></li>
                            <li role="presentation" class="divider">
                            <li class="dropdown-submenu">
                                <a tabindex="-1" href="#">Configuration</a>
                                <ul class="dropdown-menu">
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="generalConfig.php">General Configuration</a></li>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="dbConfig.php">Database Configuration</a></li>
                                </ul>
                            </li>
                          </ul>
                    </li>
                </ul>
                
                <ul class="nav pull-right" role="navigation" style="font-size: 14px;">
                    <li class="dropdown" style="border-left: none;    background-image: linear-gradient(to bottom, #C82121, #F01212);">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Settings <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="s_cp.php">Change Password</a></li>
                            <!--<li role="presentation" class="divider"></li>
                            <li role="presentation" style="background-color: #FF0000;"><a role="menuitem" tabindex="-1" href="flushDB.php" style="color: #FFFFFF; alignment-adjust: ">Flush Database</a></li> -->
                         </ul>
                    </li>
                    <li class="" style="border-left: none;background-image: linear-gradient(to bottom, #131314, #5E7A9A);">
                        <a href="logout.php" style="color: #FFFFFF;">Logout</a>
                    </li>
                </ul>
            <?php } ?>
            
      </div>
    </div>
</div>