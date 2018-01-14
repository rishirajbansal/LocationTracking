package com.locationTracking.business;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.log4j.Level;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.locationTracking.base.DatabaseConnectionManager;
import com.locationTracking.base.LoggerManager;
import com.locationTracking.dataAccess.LocationHistoryDAO;
import com.locationTracking.exceptions.BusinessEngineException;
import com.locationTracking.exceptions.DataAccessException;
import com.locationTracking.exceptions.GeoLocationException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;
import com.sun.xml.internal.bind.v2.runtime.reflect.opt.Const;

public class GeoLocation {
	
	public static LoggerManager logger = Utility.getLogger(GeoLocation.class.getName());
	

	public static LocationHistoryDAO dao = null;
	
	public static Map<String, String> cachedLocations = new HashMap<String, String>();
	
	private HttpGet post;
	
	static {
		try {
			dao = new LocationHistoryDAO();
			loadCachedLocations();
		}
		catch (Exception ex){
			logger.error("Exception occured while loading the cached locations : " + ex.getMessage());
			throw new GeoLocationException("Exception occured while loading the cached locations : " + ex.getMessage());
		}
	}
	
	
	public GeoLocation(){
		
		/*try {
			dao = new LocationHistoryDAO();
			loadCachedLocations();
		}
		catch (Exception ex){
			logger.error("Exception occured while loading the cached locations : " + ex.getMessage());
			throw new GeoLocationException("Exception occured while loading the cached locations : " + ex.getMessage());
		}*/
		
	}
	
	
	public static void loadCachedLocations(){
		
		try{
			cachedLocations = dao.fetchCachedLocations();
			
			logger.info("Loaded cached locations : " + cachedLocations.size());
			
		}
		catch (DataAccessException daEx){
			logger.error("Exception occured while loading locations from DAO layer :" + daEx.getMessage());
			throw new GeoLocationException("Exception occured while loading locations from DAO layer :" + daEx.getMessage());
		}
		
	}
	
	
	public String coordToLocation(String latitude, String longitude, String key){
		
		String location = "";
		
		//Prevent JOSN logging which lengthening the logs
		logger.getLogger("org.apache.http").setLevel(Level.ERROR);
		
		try{
			
			location = searchCachedLocations(latitude, longitude);
			
			if (Utility.safeTrim(location).equals(Constants.NOT_AVAILABLE)) {
				String url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" + latitude + ","  + longitude + "&region=es&key=" + key + "&sensor=false";
				
				HttpClient client = new DefaultHttpClient();
				post = new HttpGet(url);
				
				post.setHeader("Connection", "keep-alive");
				
				HttpResponse response = client.execute(post);
				
				BufferedReader rd = new BufferedReader(new InputStreamReader(response.getEntity().getContent()));
				
				String line = "";
				String json = "";
				
				while ((line = rd.readLine()) != null) {
					json = json + line;
				}
				 
			    post.releaseConnection();
			    
			    //logger.debug(json);
			    
			    location = parseJson(json, url);
			    
			    if (!Utility.safeTrim(location).equals(Constants.COORDS_TO_LOCATION_NOTFOUND)){
			    	cacheNewLocation(latitude, longitude, location);
			    }
			}
			
		}
		catch (Exception ex){
			logger.error("Exception occured while getting the location from coordinates from Google : " + ex.getMessage());
			throw new GeoLocationException("Exception occured while getting the location from coordinates from Google : " + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred while getting the location from coordinates from Google : " + th.getMessage());
			throw new GeoLocationException("Throwable occurred while getting the location from coordinates from Google : " + th.getMessage());
		}
		
		return location;
		
	}
	
	public String parseJson(String json, String url){
		
		String formattedAddress = "";

		try{
			Gson gson = new GsonBuilder().create();
		    LocationResults results = gson.fromJson(json, LocationResults.class);
		    String status = results.getStatus();
		    List resultsList = results.getResults();
		    
		    if (Utility.safeTrim(status).equals("OK")){
		    	if (null != resultsList){
			    	Map map = (Map)resultsList.get(0);
			    	
			    	if (null != map && map.containsKey("formatted_address")){
			    		formattedAddress = (String)map.get("formatted_address");
			    	}
			    }
		    }
		    else{
		    	logger.error("Status is not OK, status returned is : " + status);
		    	logger.error("URL : " + url);
		    	formattedAddress = Constants.COORDS_TO_LOCATION_NOTFOUND;
		    }
		    
		}
		catch (Exception ex){
			logger.error("Exception occured while getting the parsing json object : " + ex.getMessage());
			throw new GeoLocationException("Exception occured while getting the parsing json object : " + ex.getMessage());
		}
		
		return formattedAddress;
		
	}
	
	public boolean cacheNewLocation(String latitude, String longitude, String location) {
		boolean flag = true;
		
		LocationHistoryDAO dao = new LocationHistoryDAO();
		
		try{
			latitude = latitude.substring(0, latitude.indexOf(".")) + latitude.substring(latitude.indexOf(".")).substring(0, 5);
			longitude = longitude.substring(0, longitude.indexOf(".")) + longitude.substring(longitude.indexOf(".")).substring(0, 5);
			
			flag = dao.saveLocation(latitude, longitude, location);
			cachedLocations.put(latitude + "|" + longitude , location);
			
			//logger.info("New location has been cached successfully : " +  latitude + " - " + longitude + " - " + location);
			
		}
		catch (DataAccessException daEx){
			logger.error("Exception occured while caching new location from DAO layer :" + daEx.getMessage());
			throw new GeoLocationException("Exception occured while caching new location from DAO layer :" + daEx.getMessage());
		}
		
		return flag;
	}
	
	public String searchCachedLocations(String latitude, String longitude){
		
		latitude = latitude.substring(0, latitude.indexOf(".")) + latitude.substring(latitude.indexOf(".")).substring(0, 5);
		longitude = longitude.substring(0, longitude.indexOf(".")) + longitude.substring(longitude.indexOf(".")).substring(0, 5);
		
		String location = this.cachedLocations.get(latitude + "|" + longitude);
		
		if (Utility.safeTrim(location).equals(Constants.EMPTY_STRING)){
			location = Constants.NOT_AVAILABLE;
		}
		
		return location;
		
	}
	
	
	public static void main(String[] args){
		
		//GeoLocation geoLocation = new GeoLocation();
		
		DatabaseConnectionManager.instantiate();
		GeoLocation geoLocation = new GeoLocation();
		String response = "";
				
		/*while (true) {
			response = geoLocation.coordToLocation("39.5747387", "2.6581742");
			logger.debug(response);
		}*/
		
		/*String latitude = "39.5747387";
		latitude = latitude.substring(0, latitude.indexOf(".")) + latitude.substring(latitude.indexOf(".")).substring(0, 5);
		System.out.println(latitude);
		
		String longitude = "2.6581742";
		longitude = longitude.substring(0, longitude.indexOf(".")) + longitude.substring(longitude.indexOf(".")).substring(0, 5);
		System.out.println(longitude);*/
		
		response = geoLocation.coordToLocation("39.5747387", "2.6581742", "AIzaSyDEpvdHMh6gc8k4DgeJCmqOV8co1mYmS6I");
		logger.debug(response);
		
		response = geoLocation.coordToLocation("39.574738", "2.658174", "AIzaSyDEpvdHMh6gc8k4DgeJCmqOV8co1mYmS6I");
		logger.debug(response);
		
		response = geoLocation.coordToLocation("39.57473", "2.65817", "AIzaSyDEpvdHMh6gc8k4DgeJCmqOV8co1mYmS6I");
		logger.debug(response);
		
		response = geoLocation.coordToLocation("39.5747", "2.6581", "AIzaSyDEpvdHMh6gc8k4DgeJCmqOV8co1mYmS6I");
		logger.debug(response);

	
		if (response.indexOf("'") != -1){
			response = response.replaceAll("'", "''");
		}
		
		//logger.debug(response);
	}
	

}
