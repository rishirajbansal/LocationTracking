/**
 * 
 */
package com.locationTracking.generic;

import java.nio.file.FileSystems;

/**
 * @author Rishi
 *
 */
public class Constants {
	
	public static final String EMPTY_STRING										= "";
	public static final String STRING_TRUE										= "true";
	public static final String STRING_FALSE										= "false";
	
	public static final String PROPERTIES_FILE_PATH								= "PropertiesFilePath";
	public static final String CONFIGURATION_FILE_PATH							= "ConfigurationFilePath";
	
	public static final String FILE_SEPARATOR 									= FileSystems.getDefault().getSeparator();
	public static final String PIPE_SEPARATOR 									= "|";
	
	public static final String COMMON_PROPERTIES_FILE_NAME 						= "common";
	public static final String DATABASE_PROPERTIES_FILE_NAME 					= "database";
	public static final String LOGINS_PROPERTIES_FILE_NAME 						= "logins";
	public static final String MAIN_PROPERTIES_FILE_NAME 						= "main";
	
	public static final String CONFIG_PROPERTIES_FILE_TYPE 						= ".properties";
	
	/*
	 * Database configuration constants
	 */
	public static final String DATABASE_DRIVER									= "driver";
	public static final String DATABASE_URL										= "url";
	public static final String DATABASE_USERNAME								= "username";
	public static final String DATABASE_PASSWORD								= "password";
	public static final String DATABASE_AUTO_COMMIT								= "autoCommit";
	public static final String DATABASE_ACTIVE_MAX_POOL_SIZE					= "activeMaxPoolSize";
	
	/*
	 * login credentials constants
	 */
	public static final String LOGINS_COUNT										= "logins_count";
	public static final String LOGIN_CREDENTIALS								= "login";
	
	
	public static final String FILE_DOWNLOAD_TIME_START							= "start";
	public static final String FILE_DOWNLOAD_TIME_END							= "end";
	public static final String KML_ELEMENT_WHEN									= "When";
	public static final String KML_ELEMENT_COORD								= "Coord";
	public static final String KML_FILE_LOCATIONS_TIMEZONE						= "GMT-7:00";
	
	public static final String APPLICATION_TIMEZONE								= "app_timezone";
	
	public static final String COORDS_TO_LOCATION_NOTFOUND						= "NOT_OK";
	//public static final String COORDS_TO_LOCATION_NOTFOUND_DB					= "Location not available";
	public static final String COORDS_TO_LOCATION_NOTFOUND_DB					= "--";
	public static final String GOOGLE_ACCOUNT_KEY								= "google_acc_key";
	public static final String NOT_AVAILABLE									= "NA";
	public static final String THREAD_POOL_THRESHOLD							= "thread_pool_threshold";
	public static final String MONITOR_THREAD_POLL_FREQUENCY					= "monitorThreadPollFrequency";
	public static final String MULTITHREADING_MODE								= "multiThreadingMode";
	public static final String EXECUTION_FREQUENCY								= "execution_frequency";
	
	public static final String CONFIG_URL										= "confing_url";
	
	public static final String generalConfigData								= "sys_general.conf";
	public static final String dbConfigData										= "sys_db.conf";
	public static final String confUpdatedURL									= "newconfig";
	
	/*
	 * DAO constants
	 */
	public static final int WORKER_STATUS_ACTIVE								= 1;
	public static final int MANUALRETRIEVALREQUESTS_STATUS_ACTIVE				= 1;
	public static final int MANUALRETRIEVALREQUESTS_STATUS_COMPLETED			= 2;
	public static final int MANUALRETRIEVALREQUESTS_STATUS_NOTFOUND				= 3;
	public static final int MANUALRETRIEVALREQUESTS_STATUS_ERROR				= 4;
	
	
	
		

}
