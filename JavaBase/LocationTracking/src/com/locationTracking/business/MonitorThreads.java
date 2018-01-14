package com.locationTracking.business;

import java.util.Map;
import java.util.Map.Entry;
import java.util.ResourceBundle;
import java.util.Set;
import java.util.concurrent.Future;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.MonitorThreadsException;
import com.locationTracking.exceptions.ThreadManagerException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;

public class MonitorThreads extends Thread {
	
	public static LoggerManager logger = Utility.getLogger(MonitorThreads.class.getName());
	
	
	private final static String THREAD_NAME = "MonitorThread";
	
	public Map<String, Future> businessThreadStatus = null;
	
	private boolean runFlag = true;
	
	private String monitorThreadsPollFrequency;
	private long longMonitorThreadsPollFrequency;
	
	public int count;
	
	
	{
		ResourceBundle rBundleCommons = ResourceBundle.getBundle(Constants.COMMON_PROPERTIES_FILE_NAME);
		
		monitorThreadsPollFrequency = rBundleCommons.getString(Constants.MONITOR_THREAD_POLL_FREQUENCY);
		longMonitorThreadsPollFrequency = Long.parseLong(monitorThreadsPollFrequency) * 1000;
		
	}
	
	public MonitorThreads(Map<String, Future> businessThreadStatus) {
		super(THREAD_NAME);
		
		this.businessThreadStatus = businessThreadStatus;
	}
	
	/*private MonitorThreads() {
		super(THREAD_NAME);		
	}*/
	
	
	/*public static MonitorThreads createInstance(Map<String, Future> businessThreadStatus) {
		
		if (monitorThreads == null) {
			synchronized (lockObject) {
				monitorThreads = new MonitorThreads();
				MonitorThreads.businessThreadStatus = businessThreadStatus;
			}
		}

		return monitorThreads;
	}*/
	
	public void startMonitor(int count){
		
		try{
			this.count = count;

			this.start();
			
		}
		catch(Exception ex){
			logger.error("Exception occured while spawning the monitor thread : " + ex.getMessage());
			throw new MonitorThreadsException("Exception occured while spawning the monitor thread : " + ex.getMessage());
		}
		
	}
	
	public void run(){
		
		//For the first time, hold the thread so that the business thread get start executing
		try {
			Thread.sleep(5000);
			logger.debug("Monitor thread waked up from first time sleep. Will start monitoring the threads...");
		} 
		catch(InterruptedException iEx){
			logger.error("InterruptedException occurred while running the Monitor thread for first time execution : " + iEx.getMessage());
		}
		
		logger.debug("Monitor Threads count : " + this.count);
		
		while(runFlag){
			
			executeMonitor();
			
			try{
				//Make the thread sleep for the configurable time interval
				Thread.sleep(longMonitorThreadsPollFrequency);
				
			}
			catch(InterruptedException iEx){
				logger.error("InterruptedException occurred while running the Monitor thread : " + iEx.getMessage());
			}
			catch(Exception ex){
				logger.error("Exception occurred while running the Monitor thread : " + ex.getMessage());
				runFlag = false;
				throw new MonitorThreadsException("Exception occurred while running the Monitor thread : " + ex.getMessage());
			}
			
		}
		
	}
	
	public void executeMonitor(){
		
		try{

			if (null != businessThreadStatus && businessThreadStatus.size() > 0)  {
				
				for (Entry<String, Future> entry : businessThreadStatus.entrySet()){
					String name = entry.getKey();
					Future task = entry.getValue();
					
					if (task.isDone()){
						logger.debug("Thread : " + name + " has completed its job, will be terminated now.");
						businessThreadStatus.remove(name);
						
						count = count - 1;
						logger.debug("Current size of threads in working status : " + count);
						
						//Need to exit from the loop as Map has been modified
						break;
					}
				}
				
			}
			
			if (count == 0){
				runFlag = false;
				logger.debug("All threads are completed their jobs. Its ok to terminate the threadmanager.");
			}
			
		}
		catch(Exception ex){
			logger.error("Exception occurred : " + ex.getMessage());
			runFlag = false;
			throw new MonitorThreadsException("Exception occurred : " + ex.getMessage());
		}
		
	}
	
	public void terminate(){
		this.runFlag = false;
	}

}
