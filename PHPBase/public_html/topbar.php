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
    <div class="navbar-inner" style="background-color: #1C71B5;background-image: -moz-linear-gradient(top, #38B8D5, #1C71B5); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#38B8D5), to(#1C71B5)); background-image: -webkit-linear-gradient(top, #FFFFFF, #679AB0);background-image: -o-linear-gradient(top, #38B8D5, #1C71B5);background-image: linear-gradient(to bottom, #38B8D5, #1C71B5)">
        <div class="container-fluid">
            <a class="brand" href="home.php" style="float: left;padding-right: 100px;font-size: 20px;color:#FFFFFF">Location History Tracking</a>
            
            <?php 
            if (!empty($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] == 1) && !empty($_SESSION['user'])) { ?>
            
                <ul class="nav" role="navigation" style="font-size: 14px;">
                    <li class="">
                        <a href="home.php" style="color: #FFFFFF;">Home</a>
                    </li>
                    <li class="">
                        <a href="monitorAll.php" style="color: #FFFFFF;">Monitor</a>
                    </li>
                    <!--<li class="dropdown">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Monetarization <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="monitorAll.php">Monitor All</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="monitorProject.php">Project Based Monitoring</a></li>
                        </ul>
                    </li>-->
                    <li class="dropdown">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Reports <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="reportTime.php">Time based Summary</a></li>
                            <!--<li role="presentation"><a role="menuitem" tabindex="-1" href="reportDay.php">Day based summary</a></li>-->
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="reportDistance.php">Distance based Summary</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="reportProjectLocation.php">Project Location based Summary</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="reportStops.php">Stops based Summary</a></li>
                        </ul>
                    </li>
                    <!--<li class="dropdown" >
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Application Settings <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="addProject.php">Add New Project Location</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="listProjects.php">View Projects Location</a></li>
                         </ul>
                    </li> -->
                    <li class="dropdown" style="border-right: 1px solid #FFFFFF; ">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">Application Configuration <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="setGAccount.php">Setup Google Account</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="setGDriveFolder.php">Setup Google Drive Folder</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav pull-right" role="navigation" style="font-size: 14px;">
                    <li class="dropdown" style="border-left: none;    ">
                        <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown" style="color: #FFFFFF;">My Account <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="chgPwd.php">Change Password</a></li>
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