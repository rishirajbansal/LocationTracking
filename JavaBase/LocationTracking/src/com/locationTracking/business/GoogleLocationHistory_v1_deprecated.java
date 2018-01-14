package com.locationTracking.business;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.CookieHandler;
import java.net.CookieManager;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;

import javax.net.ssl.HttpsURLConnection;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.GoogleLocationHistoryException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;

///////////*************//////////

//THIS CODE IS DEPRECATED AND NOT IN USE, GOOGLE HAS UPDATED THE WAY TO RETRIEVE THE LOCATION HISTORY

///////////*************//////////

public class GoogleLocationHistory_v1_deprecated {
	
	public static LoggerManager logger = Utility.getLogger(GoogleLocationHistory_v1_deprecated.class.getName());
	
 
	private List<String> cookies;
	private HttpsURLConnection conn;
	
	private final String USER_AGENT = "Mozilla/5.0";
	
	
	public String getLocationHistory(String userid, String password, String startTime, String endTime){
		
		String response = "";
		
		String url = "https://accounts.google.com/ServiceLoginAuth";
		GoogleLocationHistory_v1_deprecated googleLocationHistory = new GoogleLocationHistory_v1_deprecated();
		
		try{
			if (!Utility.safeTrim(userid).equals(Constants.EMPTY_STRING) && !Utility.safeTrim(password).equals(Constants.EMPTY_STRING)){
				String locationHistoryURL = "https://maps.google.com/locationhistory/b/0/kml?startTime=" + startTime + "&endTime=" + endTime;
				logger.debug(locationHistoryURL);				
				
				//Turning on Cookies
				CookieHandler.setDefault(new CookieManager());
			 
				//"GET" request, to extract the form's data.
				String page = googleLocationHistory.GetPageContent(url);
				String postParams = googleLocationHistory.getFormParams(page, userid, password);
			 
				//Construct above post's content and then send a POST request for authentication
				String postResponse = googleLocationHistory.sendPost(url, postParams);
				
				if (Utility.safeTrim(postParams).indexOf("PasswordSeparation") != -1){
					logger.debug("==Dual authenication found. Will authenticate one more step...");
					postParams = googleLocationHistory.getFormParams(postResponse, userid, password);
					 
					//Construct above post's content and then send a POST request for authentication
					postResponse = googleLocationHistory.sendPost(url, postParams);
				}
				
				//Get KML
				response = googleLocationHistory.GetPageContent(locationHistoryURL);
				logger.debug("~~~~~ kml response : " + response);
				//logger.debug(response);
				
				if (null != conn) {
					conn.disconnect();
				}
			}
			else{
				logger.error("User id or password is empty");
				throw new GoogleLocationHistoryException("User id or password is empty");
			}
			
		}
		catch(GoogleLocationHistoryException glhEx){
			throw glhEx;
		}
		catch (Exception ex){
			logger.error("Exception occured while getting location history from Google" + ex.getMessage());
			throw new GoogleLocationHistoryException("Exception occured while getting location history from Google" + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred : " + th.getMessage());
			throw new GoogleLocationHistoryException("Throwable occurred : " + th.getMessage());
		}
		
		return response;
	}
	
	private String sendPost(String url, String postParams) throws Exception {
		
		URL obj = new URL(url);
		conn = (HttpsURLConnection) obj.openConnection();
	 
		// Acts like a browser
		conn.setUseCaches(false);
		conn.setRequestMethod("POST");
		conn.setRequestProperty("Host", "accounts.google.com");
		conn.setRequestProperty("User-Agent", USER_AGENT);
		conn.setRequestProperty("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		conn.setRequestProperty("Accept-Language", "en-US,en;q=0.5");
		for (String cookie : this.cookies) {
			conn.addRequestProperty("Cookie", cookie.split(";", 1)[0]);
		}
		conn.setRequestProperty("Connection", "keep-alive");
		//conn.setRequestProperty("keep-alive", "timeout=50, max=1");
		//System.setProperty("http.keepAlive","false");
		conn.setRequestProperty("Referer", "https://accounts.google.com/ServiceLoginAuth");
		conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
		conn.setRequestProperty("Content-Length", Integer.toString(postParams.length()));
	 
		conn.setDoOutput(true);
		conn.setDoInput(true);
	 
		// Send post request
		DataOutputStream wr = new DataOutputStream(conn.getOutputStream());
		wr.writeBytes(postParams);
		wr.flush();
		wr.close();
	 
		int responseCode = conn.getResponseCode();
		logger.info("\nSending 'POST' request to URL : " + url);
		logger.info("Post parameters : " + postParams);
		logger.info("Response Code : " + responseCode);
	 
		BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
		String inputLine;
		StringBuffer response = new StringBuffer();
	 
		while ((inputLine = in.readLine()) != null) {
			response.append(inputLine);
		}
		in.close();
		//logger.debug(response.toString());
		
		return response.toString();
		
	}
	
	private String GetPageContent(String url) throws Exception {
		
		URL obj = new URL(url);
		conn = (HttpsURLConnection) obj.openConnection();
	 
		// default is GET
		conn.setRequestMethod("GET");
	 
		conn.setUseCaches(false);
	 
		// act like a browser
		conn.setRequestProperty("User-Agent", USER_AGENT);
		conn.setRequestProperty("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		conn.setRequestProperty("Accept-Language", "en-US,en;q=0.5");
		if (cookies != null) {
			for (String cookie : this.cookies) {
				conn.addRequestProperty("Cookie", cookie.split(";", 1)[0]);
			}
		}
		int responseCode = conn.getResponseCode();
		logger.info("\nSending 'GET' request to URL : " + url);
		logger.info("Response Code : " + responseCode);
	 
		BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
		String inputLine;
		StringBuffer response = new StringBuffer();
	 
		while ((inputLine = in.readLine()) != null) {
			response.append(inputLine);
		}
		in.close();
	 
		// Get the response cookies
		setCookies(conn.getHeaderFields().get("Set-Cookie"));
	 
		return response.toString();
		
	}
	
	public String getFormParams(String html, String username, String password) throws UnsupportedEncodingException {
		  
		logger.debug("Extracting form's data...");
		  
		Document doc = Jsoup.parse(html);
		  
		// Google form id
		Element loginform = doc.getElementById("gaia_loginform");
		Elements inputElements = loginform.getElementsByTag("input");
		List<String> paramList = new ArrayList<String>();
		
		for (Element inputElement : inputElements){
			String key = inputElement.attr("name");
			String value = inputElement.attr("value");
			  
			if (key.equals("Email")) {
				value = username;
			}
			else if (key.equals("Passwd") || key.equals("Passwd-hidden"))
				value = password;
			/*else if (key.equals("PersistentCookie"))
				value = "no";*/
			
			paramList.add(key + "=" + URLEncoder.encode(value, "UTF-8"));
		}
		
		// build parameters list
		StringBuilder result = new StringBuilder();
		
		for (String param : paramList){
			if (result.length() == 0){
				result.append(param);
			}
			else {
				result.append("&" + param);
			}
		}
		
		return result.toString();
		
	}
	
	public static void main(String[] args) throws Exception {
		
		String url = "https://accounts.google.com/ServiceLoginAuth";
		 
		GoogleLocationHistory_v1_deprecated http = new GoogleLocationHistory_v1_deprecated();
		
		//Turning on Cookies
		CookieHandler.setDefault(new CookieManager());
	 
		//"GET" request, to extract the form's data.
		String page = http.GetPageContent(url);
		String postParams = http.getFormParams(page, "gestion2@9sistemes.com", "gestion02");
		//String postParams = http.getFormParams(page, "toni.cb@cuxach.es", "1979toni.cb");
	 
		//Construct above post's content and then send a POST request for authentication
		http.sendPost(url, postParams);
		
		//Get KML
		String result = http.GetPageContent("https://maps.google.co.in/locationhistory/b/0/kml?startTime=1435170600000&endTime=1435257000000");
		//String result = http.GetPageContent("https://www.google.co.in/maps/timeline/kml?authuser=0&pb=!1m8!1m3!1i2015!2i5!3i27!2m3!1i2015!2i5!3i27");
		
		System.out.println(result);
		
	}
	
	public List<String> getCookies() {
		return cookies;
	}
	
	public void setCookies(List<String> cookies) {
		this.cookies = cookies;
	}
	 
}
