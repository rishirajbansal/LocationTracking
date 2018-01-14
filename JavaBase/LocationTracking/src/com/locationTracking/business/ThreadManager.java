package com.locationTracking.business;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;
import java.util.concurrent.TimeUnit;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.BusinessEngineException;
import com.locationTracking.exceptions.ThreadManagerException;
import com.locationTracking.generic.Utility;


public class ThreadManager {
	
	public static LoggerManager logger = Utility.getLogger(ThreadManager.class.getName());
	
	//private static ThreadManager threadManager = null;
	
	private ExecutorService executor = null;
	
	/** Object used for locking. */
	private static Object lockObject = new Object();
	
	public static Map<String, Future> businessThreadStatus = null;
	
	
	/*private ThreadManager(){
		
		//Do Nothing
	}*/
	
	
	public ThreadManager(int threadPool){
		
		try {
			executor = Executors.newFixedThreadPool(threadPool);
			businessThreadStatus = new ConcurrentHashMap<String, Future>();
		}
		catch (Exception ex){
			logger.error("Exception occured while instanting ThreadManager : " + ex.getMessage());
			throw new ThreadManagerException("Exception occured while instanting ThreadManager : " + ex.getMessage());
		}
		
	}
	
	/*public static ThreadManager instantiate(int threadPool) { 
		
		if (threadManager == null) {
			synchronized (lockObject) {
				threadManager = new ThreadManager(threadPool);
			}
		}

		return threadManager;
	}*/

	
	public Future executeThread(Runnable thread, String name){
		
		Future result = null;
		
		try{
			
			result = executor.submit(thread);
			logger.debug("New thread spawned for : " + name);
			
			businessThreadStatus.put(name, result);
			
		}
		catch (Exception ex){
			logger.error("Exception occured while executing the thread : " + ex.getMessage());
			throw new ThreadManagerException("Exception occured while executing the thread : " + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred while executing the thread : " + th.getMessage());
			throw new ThreadManagerException("Throwable occurred while executing the thread : " + th.getMessage());
		}
		
		return result;
	}
	
	public void terminateThreadManager(){
		executor.shutdown();
	}
	
	public void terminateThreadManagerNow(){
		executor.shutdown(); // Disable new tasks from being submitted
		
		try{
			logger.debug("Thread manager is waiting for 60 seconds to get all active threads completed...");
			if (!executor.awaitTermination(60, TimeUnit.SECONDS)){
				executor.shutdownNow(); // Cancel currently executing tasks
				
				// Wait a while for tasks to respond to being cancelled
				logger.debug("Thread manager will wait for another 60 seconds (if require) to get all active threads completed...");
				if (!executor.awaitTermination(60, TimeUnit.SECONDS)){
					logger.error("Thread Manager has not terminated in 60 seconds.");
				}
				else{
					logger.debug("Thread manager is terminated successfully in second time.");
				}
			}
			else{
				logger.debug("Thread manager is terminated successfully in first time.");
			}
		}
		catch(InterruptedException iEx){
			// (Re-)Cancel if current thread also interrupted
			executor.shutdownNow();
			
			// Preserve interrupt status
			Thread.currentThread().interrupt();
		}
	}

	public static final Map<String, Future> getBusinessThreadStatus() {
		return businessThreadStatus;
	}


	public static final void setBusinessThreadStatus(Map<String, Future> businessThreadStatus) {
		ThreadManager.businessThreadStatus = businessThreadStatus;
	}


}
