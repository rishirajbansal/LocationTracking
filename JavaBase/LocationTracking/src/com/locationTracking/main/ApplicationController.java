package com.locationTracking.main;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.MissingResourceException;
import java.util.ResourceBundle;

import com.locationTracking.base.ConfigurationManager;
import com.locationTracking.base.DatabaseConnectionManager;
import com.locationTracking.base.LoggerManager;
import com.locationTracking.business.BusinessEngine;
import com.locationTracking.business.BusinessEngineSingleThreaded;
import com.locationTracking.business.GeoLocation;
import com.locationTracking.business.LocationHistoryRetrieval;
import com.locationTracking.business.MonitorThreads;
import com.locationTracking.business.ThreadManager;
import com.locationTracking.exceptions.BusinessEngineException;
import com.locationTracking.exceptions.ConfigurationManagerException;
import com.locationTracking.exceptions.DatabaseConnectionManagerException;
import com.locationTracking.exceptions.MonitorThreadsException;
import com.locationTracking.exceptions.ThreadManagerException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;

/**
 * Main Class of the program that controls the whole execution of Location Tracking history data download
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */

public class ApplicationController {
	
	private static final long serialVersionUID = 1L;

	//public static LoggerManager logger = Utility.getLogger(ApplicationController.class.getName());
	public static LoggerManager logger = Utility.getLogger("M");
	
	private static boolean shutdownFlag = true;
	
	private static boolean running = true;
	
	private static BusinessEngine engine = null;
	
	
	public static void main(String[] args){
		
		logger.info("======================================================================================");
		logger.info("*~*~*~*~*~*~*~*~*~*~*~*~* Location Tracking *~*~*~*~*~*~**~*~*~*~*~*~*");
		logger.info("======================================================================================");
		
		doProcessing();
		
	}
	
	public static void doProcessing(){
		
		BusinessEngineSingleThreaded engineSingleThreaded = null;
		LocationHistoryRetrieval locationHistoryRetrieval = null;
		ThreadManager threadManager = null;
		boolean isConfigUpdated = false;
		
		try{
			logger.info(" ");
			logger.info("******************************* Processing Ignited *******************************");
			logger.info(" ");
			
			running = true;
			//Turn on the shutdown hook
			shutdownFlag = true;
			
			initialize();
			
			ResourceBundle.clearCache();
			
			ResourceBundle rBundleCommons = ResourceBundle.getBundle(Constants.COMMON_PROPERTIES_FILE_NAME);
			
			String multiThreadedMode = rBundleCommons.getString(Constants.MULTITHREADING_MODE);
			
			if (Utility.safeTrim(multiThreadedMode).equals(Constants.STRING_TRUE)){
				logger.debug("Application is initialized in 'Multithreading' mode");
				
				/* Instantiate Thread Manager */
				String threadPoolSize = rBundleCommons.getString(Constants.THREAD_POOL_THRESHOLD);
				logger.info("Thread Pool threshold value : " + threadPoolSize);
				threadManager = new ThreadManager(Integer.parseInt(threadPoolSize));
				logger.info("Thread Pool Manager started successfully.");
				
				/*Call Business Engine*/
				engine = new BusinessEngine(threadManager);
				logger.info("BusinessEngine instantiated successfully.");
				
				/* Register Shutdown hook for graceful shutdown in case the program is terminated unexpectedly by user  */
				ApplicationController.registerShutdownHook(engine);
				
			}
			else{
				logger.debug("Application is initialized in 'Single Threaded' mode");
				
				/*Call Business Engine*/
				engineSingleThreaded = new BusinessEngineSingleThreaded();
				logger.info("BusinessEngine instantiated successfully.");
			}
			
			/* Call Manual Location History Retrieval */
			locationHistoryRetrieval = new LocationHistoryRetrieval();
			
			while (running){
				
				logger.info(" ");
				logger.info("________________________________________ NEW EXECUTION IGNITED _______________________________________________");
				
				Date startDate = new Date();
				SimpleDateFormat startDateFormat = new SimpleDateFormat("HH:mm:ss.S");
				String startDateStr = startDateFormat.format(startDate);
				long startTime = startDate.getTime();
				
				logger.info("Execution started @ " + startDateStr);
				logger.info(" ");

				
				if (Utility.safeTrim(multiThreadedMode).equals(Constants.STRING_TRUE)){
					logger.debug("Application started in 'Multithreading' mode");
					
					/*Call Business Engine*/
					int workersCount = engine.igniteProcessing();
					logger.info("BusinessEngine ignited successfully.");
					
					/* Start Monitoring thread */
					MonitorThreads monitorThreads = new MonitorThreads(ThreadManager.getBusinessThreadStatus());
					monitorThreads.startMonitor(workersCount);
					logger.debug("Monitor Threads started successfully");
					
					/* Wait until all threads done processing */
					logger.debug("Main Thread waiting for all threads done processing...");
					monitorThreads.join();
					
					monitorThreads = null;

				}
				else{
					logger.debug("Application started in 'Single Threaded' mode");
					
					/*Call Business Engine*/
					boolean flag = engineSingleThreaded.igniteProcessing();
				}
				
				/* Execute Manual Location History Retrieval */
				logger.info(" ");
				logger.info(" Execution of Manual Location history requests... ");
				locationHistoryRetrieval.igniteProcessing();
				logger.info(" ");
				
				/* Verify if the system configuration is updated from application front-end */
				isConfigUpdated = checkConfigUpdateRequired();
				
				Date endDate = new Date();
				SimpleDateFormat endDateFormat = new SimpleDateFormat("HH:mm:ss.S");
				String endDateStr = endDateFormat.format(endDate);
				long endTime = endDate.getTime();
				
				logger.info(" ");
				logger.info("Execution is Done @ " + endDateStr);
				
				long totalTimeElapsed = endTime - startTime;
				long seconds = totalTimeElapsed / 1000;
				long minutes = seconds / 60;
				long secs = seconds % 60;
				
				//logger.info("Total Processing Duration : " + minutes + " minutes " + secs + " secs") ;
				logger.info(minutes + " m " + secs + " s") ;
				
				logger.info("________________________________________ CURRENT EXECUTION COMPLETED !!!!_______________________________________________");
				logger.info(" ");
				
				/* Check if the system configuration is updated then re-initialize the program */
				if (isConfigUpdated){
					break;
				}
				
				String executionFrequency = rBundleCommons.getString(Constants.EXECUTION_FREQUENCY);
				
				checkExecutionFrequency(seconds, executionFrequency);
				logger.info("Time to execute next processing cycle");
				logger.info(" ");
				
			}//End of while
			
			/* Check if the system configuration is updated then re-initialize the program */
			if (isConfigUpdated){
				logger.info(" ");
				logger.info(" Config settings are updated from application front-end, program will stop and restart again to make the latest config changes in effect. ");
				
				//stop();
				
				logger.info(" ");
				logger.info(" Program will now be re-started... ");
				doProcessing();
				
			}
			else{
				//Terminate Business engine
				logger.debug("Busienss Engine & Thread Manager will be terminated.");
				engine.terminate();
				
				//System has completed successfully, prevent to execute shutdown hook
				shutdownFlag = false;
			}
			
		
		}
		catch(ThreadManagerException tmEx){
			logger.error("ThreadManagerException occurred : " + (Utility.safeTrim(tmEx.toString()).equals("") ? tmEx.getMessage(): tmEx.toString()) );
			logger.fatal("System will be terminated.");
			System.exit(1);
		}
		catch(MonitorThreadsException mtEx){
			logger.error("BusinessEngineException occurred somwhere while processing the business logic : " + (Utility.safeTrim(mtEx.toString()).equals("") ? mtEx.getMessage(): mtEx.toString()) );
			logger.fatal("System will be terminated.");
			System.exit(1);
		}
		catch(BusinessEngineException beEx){
			logger.error("BusinessEngineException occurred somwhere while processing the business logic : " + beEx.getMessage());
			logger.error("System will again try to execute the business engine next time");
		}
		catch(Exception ex){
			logger.fatal("Exception occurred : " + (Utility.safeTrim(ex.toString()).equals("") ? ex.getMessage(): ex.toString()) );
			logger.fatal("System will be terminated.");
			System.exit(1);
		}
		catch (Throwable th){
			logger.fatal("Error occurred : " + th.getMessage());
			logger.fatal("System will be terminated.");
			System.exit(1);
		}
		
	}
	
	public static void initialize(){
		
		try{
			/* Load Configuration Settings */
			//ConfigurationManager configManager = ConfigurationManager.getInstance();
			ConfigurationManager configManager = new ConfigurationManager();
			configManager.loadConfiguration();
			logger.info("Configuration of system is loaded and configured successfully.");
			
			/* Initialize Database's DataSource & Pool Manager */
			DatabaseConnectionManager.instantiate();
			boolean status = DatabaseConnectionManager.testDBConnection();
			if (status){
				logger.info("Database connections & pooling are configured successfully. Test Passed.");
			}
			else{
				logger.info("Database connections & pooling are FAILED to be configured successfully. Test FAILED.");
				throw new DatabaseConnectionManagerException("Database connections & pooling are FAILED to be configured successfully. Test FAILED.");
			}
			
			/* Load Cached Locations */
			GeoLocation geoLocation = new GeoLocation();
			logger.info("Geo location loaded in cache memory successfully.");
			
		}
		catch(MissingResourceException mrEx){
			logger.fatal("No resource bundle for the specified base name can be found : " + mrEx.getMessage());
			throw mrEx;
		}
		catch(DatabaseConnectionManagerException dcmEx){
			logger.fatal("DatabaseConnectionManagerException occurred in initializing the application : " + dcmEx.getMessage());
			throw dcmEx;
		}
		catch(ConfigurationManagerException cmEx){
			logger.fatal("ConfigurationManagerException occurred in initializing the application : " + cmEx.getMessage());
			throw cmEx;
		}
		
	}
	
	public static boolean checkConfigUpdateRequired(){
		
		boolean flag = false;
		
		try{
			//ConfigurationManager configManager = ConfigurationManager.getInstance();
			ConfigurationManager configManager = new ConfigurationManager();
			boolean needUpdate = configManager.verifyConfigUpdate();
			//boolean needUpdate = false;
			
			if (needUpdate){
				logger.info("System configuration is changed and need to update");
				
				configManager.loadConfiguration();
				configManager.toggleConfigUpdate();
				
				logger.info("System configuration is Updated with latest changes.");
				
				flag = true;
			}
			
		}
		catch(ConfigurationManagerException cmEx){
			logger.fatal("ConfigurationManagerException occurred in verifying config update, program will not be terminated but NEEDS ATTENTION : " + cmEx.getMessage());
		}
		
		return flag;
		
	}
	
	public static void checkExecutionFrequency(long seconds, String executionFrequency){
		
		try{
			long frequency = Long.valueOf(executionFrequency);
			frequency = frequency * 60;
			
			if (seconds >= frequency){
				//Do Nothing and resume the execution
			}
			else{
				long diff = frequency - seconds;
				
				//logger.debug(" Diff:  " + diff);

				long diffInMillis = diff * 1000;
				
				Thread.sleep(diffInMillis);
			}
		}
		catch (NumberFormatException nfEx){
			logger.fatal("Execution Frequnecy in properties file is in incorrect format. Not able to parse into Long.");
			System.exit(1);
		}
		catch (InterruptedException iEx){
			logger.error("InterruptedException occured during checking the execution Frequency :" + (Utility.safeTrim(iEx.toString()).equals("") ? iEx.getMessage(): iEx.toString()) );
		}
		catch(Exception ex){
			logger.error("Exception occured during checking the execution Frequency :" + (Utility.safeTrim(ex.toString()).equals("") ? ex.getMessage(): ex.toString()) );
			throw ex;
		}
		
	}
	
	public static void stop(){
		
		try{
			logger.info("Service Stop is requested.");
			
			running = false;
			
			//Terminate Business engine
			logger.debug("Business Engine & Thread Manager will be stopped.");
			engine.terminateNow();
			
			//System has stopped successfully, prevent to execute shutdown hook
			shutdownFlag = false;
			
			logger.info(" ");
			logger.info("Application is stopped.");
			
			logger.info("________________________________________ STOPPED !!!!______________________________________________________");
		
		}
		catch(Exception ex){
			logger.error("Exception occured while in shutdownhook : " + ex.getMessage());
			throw ex;
		}
		
	}
	
	public static void registerShutdownHook(final BusinessEngine engine){
		
		try{
			Runtime.getRuntime().addShutdownHook(new Thread(new Runnable() {
				
				@Override
				public void run() {
					try{
						if (shutdownFlag){
							logger.debug("~~~ It seems the application is interrupted and terminated forcefully, the application will now perform compulsary completion tasks and will call out all the threads, for graceful termination and to prevent any unexpected corrupt data. ~~~");
							
							running = false;
	
							//Terminate Business engine
							logger.debug("Business Engine & Thread Manager will be terminated.");
							engine.terminateNow();
							
							logger.info(" ");
							logger.info("Application is terminated.");
							
							logger.info("________________________________________ TERMINATED !!!!______________________________________________________");
							
						}
						
					}
					catch (Exception ex){
						logger.error("Exception occured while executin shutdownhook : " + ex.getMessage());
					}
				}
			},"Application shutdown Hook")); 
		}
		catch(Exception ex){
			logger.error("Exception occured while in shutdownhook : " + ex.getMessage());
			throw ex;
		}
		
	}

}
