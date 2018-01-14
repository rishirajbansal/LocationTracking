package com.locationTracking.business;

import java.util.List;
import java.util.MissingResourceException;
import java.util.ResourceBundle;
import java.util.concurrent.Future;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.dataAccess.LocationHistoryDAO;
import com.locationTracking.dataAccess.Worker;
import com.locationTracking.exceptions.BusinessEngineException;
import com.locationTracking.exceptions.DataAccessException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;


/**
 * Business class to execute core logics 
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */

public class BusinessEngine {
	
	private static final long serialVersionUID = 1L;

	public static LoggerManager logger = Utility.getLogger(BusinessEngine.class.getName());
	
	//private static BusinessEngine engine = null;
	
	/** Object used for locking. */
	private static Object lockObject = new Object();
	
	private ThreadManager threadManager;
	
	private String appTimezone;
	private String appKey;
	
	private LocationHistoryDAO dao;
	
	
	{		
		try{
			dao = new LocationHistoryDAO();
			
			ResourceBundle rBundleCommons = ResourceBundle.getBundle(Constants.COMMON_PROPERTIES_FILE_NAME);
			
			appTimezone = rBundleCommons.getString(Constants.APPLICATION_TIMEZONE);
			appKey = rBundleCommons.getString(Constants.GOOGLE_ACCOUNT_KEY);
			
		}
		catch(MissingResourceException mrEx){
			logger.error(" Resource Bundle issue : "+ mrEx.getMessage());
			throw new BusinessEngineException(" Resource Bundle issue : "+ mrEx.getMessage());
		}
		catch (Exception ex){
			logger.error("Exception occured in static initializer :" + ex.getMessage());
			throw new BusinessEngineException("Exception occured in static initializer :" + ex.getMessage());
		}
		
	}
	
	public BusinessEngine(ThreadManager threadManager){
		
		try{
			initialize(threadManager);
		}
		catch(BusinessEngineException beEx){
			throw beEx;
		}
		catch (Exception ex){
			logger.error("Exception occured while initializing Business engine : " + ex.getMessage());
			throw new BusinessEngineException("Exception occured while initializing Business engine : " + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occured while initializing Business engine :" + th.getMessage());
			throw new BusinessEngineException("Throwable occured during instantiation :" + th.getMessage());
		}
	}
	
	/*public static BusinessEngine instantiate(ThreadManager threadManager) {
		
		try{
			if (engine == null) {
				synchronized (lockObject) {
					engine = new BusinessEngine(threadManager);
				}
			}
		}
		catch(BusinessEngineException beEx){
			throw beEx;
		}
		catch (Exception ex){
			logger.error("Exception occured during instantiation :" + ex.getMessage());
			throw new BusinessEngineException("Exception occured during instantiation :" + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occured during instantiation :" + th.getMessage());
			throw new BusinessEngineException("Throwable occured during instantiation :" + th.getMessage());
		}

		return engine;
	}*/
	
	private void initialize(ThreadManager threadManager){
		
		try{
			this.threadManager = threadManager;
			
		}
		catch (Exception ex){
			logger.error("Exception occured while initializing engine :" + ex.getMessage());
			throw new BusinessEngineException("Exception occured while initializing business engine :" + ex.getMessage());
		}
		
	}
	
	
	public int igniteProcessing(){
		
		boolean flag = true;
		int size = 0;
	
		try{			
			//Fetch all the workers
			List<Worker> workers = dao.fetchAllWorkers();
			
			if (null != workers && workers.size() > 0){
				logger.debug("~~Total workers found : " + workers.size());
				size = workers.size();
				
				for (Worker worker : workers){
					
					Runnable workerTask = new BusinessTaskHandler(worker, this);
					
					Future result = threadManager.executeThread(workerTask, worker.getName());
					
				}
				
			}
			else{
				logger.debug("No active Worker found in the records, execution not required at this moment as no worker exists. ");
			}
			
		}
		catch (DataAccessException daEx){
			logger.error("Exception occured while in DAO layer :" + daEx.getMessage());
			throw new BusinessEngineException("Exception occured while in DAO layer :" + daEx.getMessage());
		}
		catch(BusinessEngineException beEx){
			throw beEx;
		}
		catch (Exception ex){
			logger.error("Exception occured while executing business engine :" + ex.getMessage());
			throw new BusinessEngineException("Exception occured while executing business engine :" + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred in Business Engine : " + th.getMessage());
			throw new BusinessEngineException("Throwable occurred in Business Engine : " + th.getMessage());
		}
		
		return size;
	}
	
	public void terminate(){
		threadManager.terminateThreadManager();
	}
	
	public void terminateNow(){
		threadManager.terminateThreadManagerNow();
	}
	
	
	public String getApptimezone() {
		return appTimezone;
	}

	public String getAppkey() {
		return appKey;
	}

	public LocationHistoryDAO getDao() {
		return dao;
	}

	

}
