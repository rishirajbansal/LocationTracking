package com.locationTracking.business;

import java.io.IOException;
import java.sql.Timestamp;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.TimeZone;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.dataAccess.LocationHistory;
import com.locationTracking.dataAccess.LocationHistoryDAO;
import com.locationTracking.dataAccess.Worker;
import com.locationTracking.exceptions.BusinessEngineException;
import com.locationTracking.exceptions.DataAccessException;
import com.locationTracking.exceptions.GeoLocationException;
import com.locationTracking.exceptions.GoogleLocationHistoryException;
import com.locationTracking.exceptions.XMLManagerException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;
import com.locationTracking.xmlManager.XMLDataBean;
import com.locationTracking.xmlManager.XMLParsingManager;


public class BusinessTaskHandler implements Runnable {
	
	public static LoggerManager logger = Utility.getLogger(BusinessTaskHandler.class.getName());
	
	private String appTimezone;
	private String appKey;
	
	private GeoLocation geoLocation;
	private LocationHistoryDAO dao;
	
	private Worker worker;
	
	
	public BusinessTaskHandler(Worker worker, BusinessEngine engine){
		
		this.appKey = engine.getAppkey();
		this.appTimezone = engine.getApptimezone();
		this.geoLocation = new GeoLocation();
		this.dao = engine.getDao();
		
		this.worker = worker;
		
	}
	

	@Override
	public void run() {
		
		String kml = "";
		
		try{
			logger.debug("Retrieving location history for : ~~ " + worker.getName() + " ~~");
			
			String startTime = getTime(Constants.FILE_DOWNLOAD_TIME_START);
		    String endTime = getTime(Constants.FILE_DOWNLOAD_TIME_END);
		    logger.debug("Start Time: " + startTime + " = " + new Timestamp(Long.valueOf(startTime)));
		    logger.debug("End Time: " + endTime + " = " + new Timestamp(Long.valueOf(endTime)));
			
			String day = getCurrentDay();
			//logger.debug("Current Day: " + day);
			
			try{
				GoogleLocationHistory googleLocationHistory = new GoogleLocationHistory();
			    kml = googleLocationHistory.getLocationHistory(worker.getEmail(), worker.getDecodedPassword(), day);
			}
			catch(GoogleLocationHistoryException glhEx){
				logger.error(glhEx.getMessage());
				logger.debug("GoogleLocationHistoryException occurred but not a fatal error, execution will not be terminated and resume for other workers.");
				if (Utility.safeTrim(glhEx.getMessage()).indexOf(GoogleLocationHistory.HTTP_NOTOK_ERROR) != -1){
					logger.debug("Probably worker credentials are not correct for this worker.");
				}
			}
			
			if (Utility.safeTrim(kml).equals(Constants.EMPTY_STRING)){
		    	logger.debug("Unable to retrieve data as it was responded with empty results");
			}
		    else{
		    	//Process the KML Data
		    	try{
		    		Map<String, List<String>> hmParsedXMLData = processHistoryFile(kml);
					
					if (null != hmParsedXMLData && hmParsedXMLData.size() > 0){
						boolean saved = saveToDatabase(hmParsedXMLData, worker, startTime, endTime);
						if (saved) {
							logger.info("Location History saved succesfully.");
						}
						else{
							logger.info("No location history data found for the time duration.");
						}
					}
		    	}
		    	catch(XMLManagerException xmlEx){
					logger.error(xmlEx.getMessage());
					logger.debug("Not a fatal error, execution will not be terminated and resume for other workers.");
				}
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
			logger.error("Exception occured while executing business task handler :" + ex.getMessage());
			throw new BusinessEngineException("Exception occured while executing business task handler :" + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred in Business task handler : " + th.getMessage());
			throw new BusinessEngineException("Throwable occurred in Business task handler : " + th.getMessage());
		}
		
	}
	
	public Map<String, List<String>> processHistoryFile(String kml) throws IOException{
		
		Map<String, List<String>> hmParsedXMLData = null;
		
		try{
			
			//String xmlData = FileUtils.readFileToString(new File("C:\\Development\\BusinessProjects\\Guru\\GoogleMaps_CarlosOtin_Project-1143728\\Code_Repository\\Workspace\\LocationTracking\\JavaBase\\LocationTracking\\kml\\history-06-24-2015.xml"));
			
			kml = kml.replaceFirst("<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\">", 
							"<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">");
			String xmlData = kml;
			
			List<XMLDataBean> alXMLData = new ArrayList<XMLDataBean>();
			
			populateXMLDataBeanList(alXMLData);
			
			XMLParsingManager xmlParsingManager = new XMLParsingManager(xmlData, alXMLData, false);
			hmParsedXMLData = xmlParsingManager.parseXMLMessageForMultipleOccurences();
			
		}
		catch (XMLManagerException xmlEx){
			logger.error("(processHistoryFile) Exception occured while processing history file :" + xmlEx.getMessage());
			throw xmlEx;
		}
		
		return hmParsedXMLData;
		
	}
	
	public boolean saveToDatabase(Map<String, List<String>> hmParsedXMLData, Worker worker, String startTime, String endTime){
		
		boolean flag = true;
		String[] arr1 = new String[2];
		String[] arr2 = new String[2];
		String[] arr3 = new String[2];
		LocationHistory locationHistory = null;
		List<LocationHistory> alLocationHistory = new ArrayList<LocationHistory>();
		
		try{
			
			List<String> timeDurationsList = hmParsedXMLData.get(Constants.KML_ELEMENT_WHEN);
			List<String> coordsDurationsList = hmParsedXMLData.get(Constants.KML_ELEMENT_COORD);
			
			long startTimeInMillis = Long.parseLong(startTime);
			long endTimeInMillis = Long.parseLong(endTime);
			
			if (null != timeDurationsList && timeDurationsList.size() > 0){
				
				//LocationHistoryDAO dao = new LocationHistoryDAO();

				//Check if previous history exists for that date to avoid redundant records for same date
				dao.deletePreviousHistory(worker);
				
				//Insert into history execution
				int idHistoryExecution = dao.saveHistoryExecution(worker);
				String preLatitude = "";
				String preLongitude = "";
				
				logger.debug("Creating location history objects which also inlcude JSON objects fetch and parse...");
				
				for (int i = 0; i < timeDurationsList.size(); i++){
					String timestamp = timeDurationsList.get(i);
					arr1 = timestamp.split("T");
					arr2 = arr1[1].split("\\.");
					timestamp = arr1[0] + " "+ arr2[0];
					String kmlTimeZone = "GMT" + arr2[1].substring(3);
					
					String coords = coordsDurationsList.get(i);
					arr3 = coords.split(" ");
					String longitude = arr3[0];
					String latitude = arr3[1];
					
					/*[BEGIN] Google updated the location history retrieval way*/
					//Check if the KML timestamp falls between today's (App Time zone) start time and end time
					long timestampInMillis = getTimestampInMillis(timestamp);
					boolean isValid = checkKMLTimestamp(timestampInMillis, startTimeInMillis, endTimeInMillis); 
					/*[END] Google updated the location history retrieval way*/
					
					if (isValid){
						//Commenting this condition as restricting common locations will not save the timestamps which keeps changing
						//if(!Utility.safeTrim(latitude).equals(preLatitude) && !Utility.safeTrim(longitude).equals(preLongitude)){
							locationHistory = new LocationHistory();
							locationHistory.setIdworkers(worker.getWorkerId());
							locationHistory.setIdHistoryExecution(idHistoryExecution);
							locationHistory.setTimestampString(timestamp);
							locationHistory.setLatitude(latitude);
							locationHistory.setLongitude(longitude);
							
							locationHistory.setFormattedTimestamp(formatKMLTimestamp(timestamp, kmlTimeZone));
							locationHistory.setLocation(coordsToLocation(latitude, longitude));
							locationHistory.setApplicationBasedTimestamp(applicationTimestamp(timestamp, kmlTimeZone));
							
							alLocationHistory.add(locationHistory);
							
							//Not using preLatitude & preLongitude
							preLatitude = latitude;
							preLongitude = longitude;
						//}
					}
					
				}
				
				logger.debug("Creation of location history objects done.");
				
				dao.saveLocationHistory(alLocationHistory, worker);
				
				//Save for Latest Location History
				dao.saveLatestLocationHistory(alLocationHistory.get(alLocationHistory.size() - 1), worker);
			}
			else{
				flag = false;
			}
			
		}
		catch (DataAccessException ex){
			logger.error("(saveToDatabase) Exception occured while saving in the database :" + ex.getMessage());
			throw new BusinessEngineException("(saveToDatabase) Exception occured while saving in the database :" + ex.getMessage());
		}
		catch (Exception ex){
			logger.error("(saveToDatabase) Exception occured while saving in the database :" + ex.getMessage());
			throw new BusinessEngineException("(saveToDatabase) Exception occured while saving in the database :" + ex.getMessage());
		}
		
		return flag;
	}
	
	public void populateXMLDataBeanList(List<XMLDataBean> alXMLDataBean){
		
		XMLDataBean xmlDataBean = new XMLDataBean();
		xmlDataBean.setName(Constants.KML_ELEMENT_WHEN);
		xmlDataBean.setType(XMLDataBean.XMLElementTypes.XML_ELEMENT_TYPE_ELEMENT);
		xmlDataBean.setValue("");
		
		xmlDataBean.setXPath("/kml:kml/kml:Document/kml:Placemark/gx:Track/kml:when");
		
		alXMLDataBean.add(xmlDataBean);
		
		xmlDataBean = new XMLDataBean();
		xmlDataBean.setName(Constants.KML_ELEMENT_COORD);
		xmlDataBean.setType(XMLDataBean.XMLElementTypes.XML_ELEMENT_TYPE_ELEMENT);
		xmlDataBean.setValue("");
		
		xmlDataBean.setXPath("/kml:kml/kml:Document/kml:Placemark/gx:Track/gx:coord");
		alXMLDataBean.add(xmlDataBean);
		
	}
	
	public String getTime(String mode){
		String time = "";
		Calendar cal = Calendar.getInstance();
		
		if (Utility.safeTrim(mode).equals(Constants.FILE_DOWNLOAD_TIME_START)){
			/*cal.set(Calendar.DATE, 29);
			cal.set(Calendar.MONTH, 5);*/
			
			//cal.set(Calendar.MONTH, 6);
			//cal.set(Calendar.DATE, 30);
			//cal.set(Calendar.HOUR, -12);
			cal.set(Calendar.HOUR_OF_DAY, 0);
			cal.set(Calendar.MINUTE, 0);
			cal.set(Calendar.SECOND, 0);
			cal.set(Calendar.MILLISECOND, 0);
			//cal.setTimeZone(TimeZone.getTimeZone(Constants.KML_FILE_LOCATIONS_TIMEZONE));	
			cal.setTimeZone(TimeZone.getTimeZone(this.appTimezone));
			
			Timestamp t = new Timestamp(cal.getTimeInMillis());
			time = Long.toString(cal.getTimeInMillis());
			
			logger.info("Timezone : " + cal.getTimeZone());
		}
		else if(Utility.safeTrim(mode).equals(Constants.FILE_DOWNLOAD_TIME_END)){
			/*cal.set(Calendar.DATE, 29);
			cal.set(Calendar.MONTH, 5);*/
			
			//cal.set(Calendar.MONTH, 6);
			//cal.set(Calendar.DATE, 30);
			//cal.set(Calendar.HOUR, 12);
			cal.set(Calendar.HOUR_OF_DAY, 24);
			cal.set(Calendar.MINUTE, 0);
			cal.set(Calendar.SECOND, 0);
			cal.set(Calendar.MILLISECOND, 0);
			//cal.setTimeZone(TimeZone.getTimeZone(Constants.KML_FILE_LOCATIONS_TIMEZONE));
			cal.setTimeZone(TimeZone.getTimeZone(this.appTimezone));
			
			Timestamp t = new Timestamp(cal.getTimeInMillis());
			time = Long.toString(cal.getTimeInMillis());
		}
		else{
			throw new BusinessEngineException("(getTime) Wrong time mode");
		}
		
		return time;
		
	}
	
	public String getCurrentDay(){
		
		Calendar cal = Calendar.getInstance();
		
		/*cal.set(Calendar.DATE, 29);
		cal.set(Calendar.MONTH, 5);*/
		
		cal.set(Calendar.HOUR_OF_DAY, 0);
		cal.set(Calendar.MINUTE, 0);
		cal.set(Calendar.SECOND, 0);
		cal.set(Calendar.MILLISECOND, 0);
		cal.setTimeZone(TimeZone.getTimeZone(this.appTimezone));
		
		int year = cal.get(Calendar.YEAR);
		int month = cal.get(Calendar.MONTH);
		int day = cal.get(Calendar.DATE);
		
		String currentDay = "1i" + year + "!2i" + month + "!3i" + day;
		
		return currentDay;
		
	}
	
	public long getTimestampInMillis(String timestamp) {
		
		long millis = 0l;
		
		try{
			SimpleDateFormat origDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
			//origDateFormat.setTimeZone(TimeZone.getTimeZone(Constants.KML_FILE_LOCATIONS_TIMEZONE));
			origDateFormat.setTimeZone(TimeZone.getTimeZone(this.appTimezone));
			Date date = origDateFormat.parse(timestamp);
			millis = date.getTime();
		}
		catch(ParseException pEx){
			throw new BusinessEngineException("(getTimestampInMillis) Problem occurred in getting millis for timestamp : " + pEx.toString());
		}
		
		return millis;
		
	}
	
	public String coordsToLocation(String latitude, String longitude){
		
		String location = "";
		
		try{
			location = geoLocation.coordToLocation(latitude, longitude, this.appKey);
			
			if (Utility.safeTrim(location).equals(Constants.COORDS_TO_LOCATION_NOTFOUND)){
				location = Constants.COORDS_TO_LOCATION_NOTFOUND_DB;
			}
			else{
				if (location.indexOf("'") != -1){
					location = location.replaceAll("'", "''");
				}
			}
		}
		catch(GeoLocationException glEx){
			logger.error("(coordsToLocation) Problem occured in converting coords to location : " + glEx.getMessage());
		}
		
		return location;
		
	}
	
public boolean checkKMLTimestamp (long millis, long starttime, long endtime) throws Exception{
		
		if (millis > starttime && starttime < endtime){
			return true;
		}
		else{
			return false;
		}
		
	}
	
	public String formatKMLTimestamp(String timestamp, String kmlTimeZone){
		
		String formattedTimestamp = "";
		
		try{
			//Get the date in KML time zone
			SimpleDateFormat origDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
			//origDateFormat.setTimeZone(TimeZone.getTimeZone(Constants.KML_FILE_LOCATIONS_TIMEZONE));
			origDateFormat.setTimeZone(TimeZone.getTimeZone(kmlTimeZone));
			Date date = origDateFormat.parse(timestamp);
			
			//Convert & Format the date into application time zone
			DateFormat formattedDateFormat = DateFormat.getDateTimeInstance(DateFormat.MEDIUM, DateFormat.MEDIUM, new Locale("en"));
			formattedDateFormat.setTimeZone(TimeZone.getTimeZone(this.appTimezone));
			formattedTimestamp = formattedDateFormat.format(date);
		}
		catch(ParseException pEx){
			throw new BusinessEngineException("(formatKMLTimestamp) Problem occurred in formatting date : " + pEx.toString());
		}
		
		return formattedTimestamp;
		
	}
	
	public String applicationTimestamp(String timestamp, String kmlTimeZone){
		
		String updatedTimestamp = "";
		
		try{
			//Get the date in KML time zone
			SimpleDateFormat origDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
			//origDateFormat.setTimeZone(TimeZone.getTimeZone(Constants.KML_FILE_LOCATIONS_TIMEZONE));
			origDateFormat.setTimeZone(TimeZone.getTimeZone(kmlTimeZone));
			Date date = origDateFormat.parse(timestamp);
			
			//Convert the date into application time zone
			SimpleDateFormat updatedDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
			updatedDateFormat.setTimeZone(TimeZone.getTimeZone(this.appTimezone));
	        updatedTimestamp = updatedDateFormat.format(date);
		}
		catch(ParseException pEx){
			throw new BusinessEngineException("(applicationTimestamp) Problem occurred in converting date : " + pEx.toString());
		}
		
		return updatedTimestamp;
		
	}

}
