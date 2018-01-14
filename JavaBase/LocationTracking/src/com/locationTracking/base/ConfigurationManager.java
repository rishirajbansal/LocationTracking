package com.locationTracking.base;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.nio.file.FileSystems;
import java.util.ArrayList;
import java.util.List;
import java.util.ResourceBundle;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.HttpClientBuilder;
import org.apache.log4j.Level;

import com.locationTracking.exceptions.ConfigurationManagerException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;

/**
 * @author Rishi
 *
 */

public class ConfigurationManager {
	
	public static LoggerManager logger = Utility.getLogger(ConfigurationManager.class.getName());
	
	private final String USER_AGENT = "Mozilla/5.0";
	private HttpClient client = HttpClientBuilder.create().build();
	
	private static final String url;
	private static final String generalConfigURL;
	private static final String dbConfigURL;
	private static final String confUpdatedURL;
	private static final String toggleConfUpdatedURL;
	
	private static ConfigurationManager configManager = null;
	
	/** Object used for locking. */
	private static Object lockObject = new Object();
	
	
	static{
		ResourceBundle rBundle = ResourceBundle.getBundle(Constants.MAIN_PROPERTIES_FILE_NAME);
		
		url = rBundle.getString(Constants.CONFIG_URL);
		generalConfigURL = url + "/conf/" + Constants.generalConfigData;
		dbConfigURL = url + "/conf/" + Constants.dbConfigData;
		confUpdatedURL = url + "/conf/" + Constants.confUpdatedURL;
		toggleConfUpdatedURL = url + "/public_html/admin/toggleNewConfig.php";
		
	}
	
	
	/*public static ConfigurationManager getInstance()  {
		
		if (null == configManager) {
			synchronized(lockObject){
				configManager = new ConfigurationManager();
			}
		}

		return configManager;
	}*/

	
	public void loadConfiguration() throws ConfigurationManagerException {
		
		String generalConfigFile = "";
		String dbConfigFile = "";
		
		try{
			logger.info("Loading and setting System Configuration...");
			
			File file = new File(System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "config");
			if (file.exists()){
				logger.debug(file.getCanonicalPath());
				generalConfigFile = System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "config" + FileSystems.getDefault().getSeparator() + Constants.COMMON_PROPERTIES_FILE_NAME + Constants.CONFIG_PROPERTIES_FILE_TYPE;
				dbConfigFile = System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "config" + FileSystems.getDefault().getSeparator() + Constants.DATABASE_PROPERTIES_FILE_NAME + Constants.CONFIG_PROPERTIES_FILE_TYPE;
			}
			else{
				/* Properties file need to put inside 'Location Tracking' folder as the exe from installer is executed one dir up from Location Tracking */
				logger.debug(file.getCanonicalPath());
				generalConfigFile = System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "LocationTracking" + FileSystems.getDefault().getSeparator() + "config" + FileSystems.getDefault().getSeparator() + Constants.COMMON_PROPERTIES_FILE_NAME + Constants.CONFIG_PROPERTIES_FILE_TYPE;
				dbConfigFile = System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "LocationTracking" + FileSystems.getDefault().getSeparator() + "config" + FileSystems.getDefault().getSeparator() + Constants.DATABASE_PROPERTIES_FILE_NAME + Constants.CONFIG_PROPERTIES_FILE_TYPE;
			}
						
			List<String> generalConfigData = getRemoteConfigData(generalConfigURL);
			List<String> dbConfigData = getRemoteConfigData(dbConfigURL);
			
			if (generalConfigData.size() <= 2 || dbConfigData.size() <= 2){
				logger.error(" Config data on application server is corrupted. ");
				throw new ConfigurationManagerException(" Config data on application server is corrupted. ");
			}
			
			setLocalConfig(generalConfigData, generalConfigFile);
			setLocalConfig(dbConfigData, dbConfigFile);

			
		}
		catch(Exception ex){
			logger.error(" Exception occured in loadConfiguration while loading config data : " + ex.getMessage());
			throw new ConfigurationManagerException(" Exception occured in loadConfiguration while loading config data : " + ex.getMessage());
		}
		catch(Throwable th){
			logger.error(" Throwable occured in loadConfiguration while loading config data : " + th.getMessage());
			throw new ConfigurationManagerException(" Throwable occured in loadConfiguration while loading config data : " + th.getMessage());
		}
		
	}
	
	public boolean verifyConfigUpdate(){
		
		boolean flag = false;
		
		try{
			logger.getLogger("org.apache.http").setLevel(Level.ERROR);
			
			HttpGet request = new HttpGet(confUpdatedURL);

			request.setHeader("User-Agent", USER_AGENT);
			request.setHeader("Accept", "*/*");
			request.setHeader("Accept-Language", "en-US,en;q=0.5");	
			
			HttpResponse response = client.execute(request);
			int responseCode = response.getStatusLine().getStatusCode();
			
			//logger.info("\nConfig 'GET' request : " + configURL);
			logger.info("Response Code for verfiy config update : " + responseCode);
			
			if (responseCode == 200){
				flag = true;
			}
			
		}
		catch(Exception ex){
			logger.error(" Exception occured in verifying config update : " + ex.getMessage());
			throw new ConfigurationManagerException(" Exception occured in verifying config update : " + ex.getMessage());
		}
		
		return flag;
		
	}
	
	private List<String> getRemoteConfigData(String configURL){
		
		List<String> alConfigData = new ArrayList<String>();
		
		try{
			logger.getLogger("org.apache.http").setLevel(Level.ERROR);
			
			HttpGet request = new HttpGet(configURL);

			request.setHeader("User-Agent", USER_AGENT);
			request.setHeader("Accept", "*/*");
			request.setHeader("Accept-Language", "en-US,en;q=0.5");	
			
			HttpResponse response = client.execute(request);
			int responseCode = response.getStatusLine().getStatusCode();
			
			//logger.info("\nConfig 'GET' request : " + configURL);
			logger.info("Response Code : " + responseCode);
			
			BufferedReader rd = new BufferedReader(new InputStreamReader(response.getEntity().getContent()));
			
			String line = "";
			
			while ((line = rd.readLine()) != null) {
				if (!Utility.safeTrim(line).equals(Constants.EMPTY_STRING)){
					alConfigData.add(line);
				}
			}
			
		}
		catch(Exception ex){
			logger.error(" Exception occured in getConfigData while retrieving config data : " + ex.getMessage());
			throw new ConfigurationManagerException(" Exception occured in getConfigData while retrieving config data : " + ex.getMessage());
		}
		
		return alConfigData;
		
	}
	
	private void setLocalConfig(List<String> alConfigData, String configFile) throws Exception{
		
		BufferedWriter outputWriter = null;
		
		try{
			File file = new File(configFile);
			
			if (file.exists()){
				file.delete();
	    	}
			
			outputWriter = new BufferedWriter(new FileWriter(configFile));
			
			for (String data : alConfigData){
				outputWriter.write(data);
				outputWriter.newLine();
			}
		}
		catch (IOException ioEx){
			logger.error(" Exception occured in setLocalConfig while setting config data : " + ioEx.getMessage());
			throw new ConfigurationManagerException(" Exception occured in setLocalConfig while setting config data : " + ioEx.getMessage());
		}
		finally {	
			if (outputWriter != null) 
				outputWriter.close();
		}
		
		return;
		
	}
	
	public void toggleConfigUpdate(){
		
		try{
			logger.getLogger("org.apache.http").setLevel(Level.ERROR);
			
			HttpPost request = new HttpPost(toggleConfUpdatedURL);

			request.setHeader("User-Agent", USER_AGENT);
			request.setHeader("Accept", "*/*");
			request.setHeader("Accept-Language", "en-US,en;q=0.5");	
			
			HttpResponse response = client.execute(request);
			int responseCode = response.getStatusLine().getStatusCode();
			
			//logger.info("\nConfig 'GET' request : " + configURL);
			logger.info("Response Code for toggle config update : " + responseCode);
			
		}
		catch(Exception ex){
			logger.error(" Exception occured in toggleConfigUpdate : " + ex.getMessage());
			throw new ConfigurationManagerException(" Exception occured in toggleConfigUpdate : " + ex.getMessage());
		}
		
		return;
		
	}

}
