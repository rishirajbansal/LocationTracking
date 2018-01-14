<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'ReportGenerator.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'DistanceSummary.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'ProjectLocationSummary.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Project.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';
include_once 'functions.php';
include_once 'sessionMgmt.php';

date_default_timezone_set(Config::$timezone);

if (isset($_GET['report']) && $_GET['report'] == 'distanceSummary'){
    
    $reportData = $_SESSION['reportData'];
    $reportInputs = new Inputs();
    $reportInputs = $_SESSION['reportInputs'];
        
    $reportGenerator = new ReportGenerator();
    $reportGenerator->generateDistanceSummaryReport($reportData, $reportInputs, $_GET['type']);
}
else if (isset($_GET['report']) && $_GET['report'] == 'projectLocationSummary'){
    
    $reportData1 = new ProjectLocationSummary();
    $reportData1 = $_SESSION['reportData1'];
    $reportData2 = new Project();
    $reportData2 = $_SESSION['reportData2'];
    $reportInputs = new Inputs();
    $reportInputs = $_SESSION['reportInputs'];

    $reportGenerator = new ReportGenerator();
    $reportGenerator->generateProjectLocationSummaryReport($reportData1, $reportData2, $reportInputs, $_GET['type']);
    
}
else if (isset($_GET['report']) && $_GET['report'] == 'timeSummary'){
    
    $reportData = $_SESSION['reportData'];
    $reportInputs = new Inputs();
    $reportInputs = $_SESSION['reportInputs'];

    $reportGenerator = new ReportGenerator();
    $reportGenerator->generateTimeSummaryReport($reportData, $reportInputs, $_GET['type']);
    
}
else if (isset($_GET['report']) && $_GET['report'] == 'stopsSummary'){
    
    $reportData = $_SESSION['reportData'];
    $reportInputs = new Inputs();
    $reportInputs = $_SESSION['reportInputs'];

    $reportGenerator = new ReportGenerator();
    $reportGenerator->generateStopsSummaryReport($reportData, $reportInputs, $_GET['type']);
    
}

?>