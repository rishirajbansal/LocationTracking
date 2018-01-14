<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'JobReporting.php';
require_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'gapi'.DIRECTORY_SEPARATOR.'Client.php';
require_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'gapi'.DIRECTORY_SEPARATOR.'Service'.DIRECTORY_SEPARATOR.'Drive.php';


session_start();

$jobReporting = new JobReporting();

//Check if google account for Drive is being updated from web application, if yes, then don't validate the token this time
if (file_exists(ACCOUNT_BEING_UPDATED_PATH)) {
    printf('Google account is being updated from web application, token would not be validated in this cycle.');
    
}
else{
    $client = getClient($jobReporting);

    if (isset($client)){
        validateToken($client, $jobReporting);
    }
    else{
        printf('Failed to refresh token. Please contact administrator.');
    }
    
}


function getClient(JobReporting $jobReporting) {

    $client = $jobReporting->getGoogleClient(Config::$googleOAuthRedirectUri_refreshToken);

    // Load previously authorized credentials from a file.
    $credentialsPath = $jobReporting->expandHomeDirectory(CREDENTIALS_PATH);

    if (file_exists($credentialsPath)) {
        $accessToken = file_get_contents($credentialsPath);
        $client->setAccessToken($accessToken);

    }
    else{
        printf('Credentials file not found. Please contact administrator. ');
        return NULL;
    }

    return $client;

}

function validateToken(Google_Client $client, JobReporting $jobReporting) {
    
    try{
        if ($client->isAccessTokenExpired()) {
            printf('Token Has expired, need to refresh...<br/><br/>');
            
            $refreshToken = file_get_contents(TOKEN_PATH);
            $client->refreshToken($refreshToken);
            
            $accessToken = $client->getAccessToken();
            $_SESSION['access_token'] = $client->getAccessToken();
            
            printf('Updating credentials file...<br/>');
            $credentialsPath = $jobReporting->expandHomeDirectory(CREDENTIALS_PATH);
            file_put_contents($credentialsPath, $accessToken);
            //printf("credentials file saved to : %s<br/>", $credentialsPath);
            printf("credentials file saved.<br/><br/>");
            
            $token = json_decode($accessToken, true);
            
            if (array_key_exists('refresh_token', $token)) {
                printf('Updating Token file with ~refresh~ type...<br/>');
                file_put_contents(TOKEN_PATH, $token['refresh_token']);
                //printf("Token file with ~refresh~ type saved to : %s<br/><br/>", TOKEN_PATH);
                printf("Token file with ~refresh~ type saved.<br/><br/>");
            }
            else {
                printf('Updating Token file with ~access~ type...<br/><br/>');
                file_put_contents(TOKEN_PATH, $token['access_token']);
                //printf("Token file with ~access~ type saved to : %s<br/><br/>", TOKEN_PATH);
                printf("Token file with ~access~ type saved.<br/><br/>");
            }
        }
        else{
            printf('Token has not expired yet, no need to refresh.<br/>');
        }
        
    } 
    catch (Exception $ex) {
        print $ex->getMessage();
    }
    
}


?>

