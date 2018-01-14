# LocationTracking

Overview

A stand alone program is implemented which uses the worker credentials to login its google account and download the KML file. All this process is automated and no human intervention is required, no google APIs dependencies and no need to buy the license. User don’t need to login into the account to get the file, everything is automated and managed by the program. One of the main feature of this program is, you can add as many workers as you want and anytime without changing the code. This program will run in the background and can be scheduled to run on specific time intervals based on Task scheduler.

Functions

	This program extracts location history records from downloaded KML file, processes them in a way to filter out the timestamps & coordinates and stores them into the database. 

	This program retrieves the location history records from the google account based on the current day from the beginning time ‘12:00:00 AM’ to day end time ’12:00:00 PM’. This timestamp and time zone has been set based on the google account settings.

	It filter out the records from the KML if the worker is on same location on different timestamps. For instance, 
Record 1 : 02:05:00	39.56565	2.565565
Record 2 : 02:06:00	39.56565	2.565565
Record 3 : 02:07:00	39.57565	2.585565

It will only consider Record 1 and Record 3.

	If the program is executed multiple times for the same date, it will not create redundant records for already fetched location history data. It will first check if the records for the current date already exists in the database, if records exists, it will flush out those records from the database before inserting new ones.

	Worker login credentials are stored in the configuration file logins.properties. This program reads this file to get the workers’ credentials. This file is used to add ‘New’ worker credentials, to delete existing, or to update existing workers’ credentials.

	This program is set to attempt 3 tries in case if it fails to retrieve location history data from google account for any reason. If for any reason, if it fails to get the data from the google account it will wait for next 5 minutes and then again try, it will repeat this until it gets the data or maximum of 3 attempts has tried. After that this program will run in next execution cycle.

	Proper Exception Handling has been managed in this program, if any issue or unwanted scenarios occurs during the execution of the program it will get recorded in the logs file which can be used for debugging and finding the cause of the issue. 

	This program is based on Java technology stack.
