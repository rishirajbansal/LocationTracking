package com.locationTracking.business;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.CookieHandler;
import java.net.CookieManager;
import java.net.CookiePolicy;
import java.net.CookieStore;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import javax.net.ssl.HttpsURLConnection;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.GoogleLocationHistoryException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;

public class GoogleLocationHistory {
	
	public static LoggerManager logger = Utility.getLogger(GoogleLocationHistory.class.getName());
	
	private static final ThreadLocal<CookieManager> controlledCookieManager = new ThreadLocal<CookieManager>();
	private final CookieManager customCookieManager = new CookieManager();
	
	static {
	    CookieHandler.setDefault(new DelegatingCookieManager());
	}
	
 
	private List<String> cookies;
	private HttpsURLConnection conn;
	private String connResponse;
	
	private final String USER_AGENT = "Mozilla/5.0";
	
	public final static String HTTP_NOTOK_ERROR = "Response code is not 200/OK, cannot proceed further for this request. Response Code received: ";
	
	
	public String getLocationHistory(String userid, String password, String day){
		
		String response = "";
		
		String url = "https://accounts.google.com/ServiceLoginAuth";
		GoogleLocationHistory googleLocationHistory = new GoogleLocationHistory();
		
		try{
			if (!Utility.safeTrim(userid).equals(Constants.EMPTY_STRING) && !Utility.safeTrim(password).equals(Constants.EMPTY_STRING)){
				
				//String locationHistoryURL = "https://maps.google.com/locationhistory/b/0/kml?startTime=" + startTime + "&endTime=" + endTime;
				String locationHistoryURL = "https://www.google.com/maps/timeline/kml?authuser=0&pb=!1m8!1m3!" + day + "!2m3!" + day;
				logger.debug(locationHistoryURL);

				//Turning on Cookies
				//The following code was creating issue in handling multiple threads, it was setting the cookie globally which was then used by other threads
				//CookieHandler.setDefault(new CookieManager());
				controlledCookieManager.set(googleLocationHistory.customCookieManager);
			 
				//"GET" request, to extract the form's data.
				String basePage = googleLocationHistory.GetPageContent(url, 1);
				
				String postParams = googleLocationHistory.getFormParams(basePage, userid, password);
				
				//Construct above post's content and then send a POST request for authentication
				String postResponse = googleLocationHistory.sendPost(url, postParams, 2);
				
				if (Utility.safeTrim(postParams).indexOf("PasswordSeparation") != -1){
					logger.debug("== Dual authenication found. Will authenticate one more step...");
					postParams = googleLocationHistory.getFormParams(postResponse, userid, password);
					 
					//Construct above post's content and then send a POST request for dual authentication
					postResponse = googleLocationHistory.sendPost(url, postParams, 3);
				}
				
				//Get KML
				response = googleLocationHistory.GetPageContent(locationHistoryURL, 4);
				logger.debug("~~~~~ K Response : " + response);
				
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
			logger.error("Exception occured while getting location history from Google: " + ex.getMessage());
			throw new GoogleLocationHistoryException("Exception occured while getting location history from Google: " + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred : " + th.getMessage());
			throw new GoogleLocationHistoryException("Throwable occurred : " + th.getMessage());
		}
		
		return response;
	}
	
	private String sendPost(String url, String postParams, int ctr) throws Exception {
		
		URL obj = new URL(url);
		conn = (HttpsURLConnection) obj.openConnection();
	 
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
		
		StringBuffer response = new StringBuffer();
		if (responseCode == HttpURLConnection.HTTP_OK){
			BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
			String inputLine;
		 
			while ((inputLine = in.readLine()) != null) {
				response.append(inputLine);
			}
			in.close();
		}
		else{
			throw new GoogleLocationHistoryException("~" + ctr + ": " + HTTP_NOTOK_ERROR + responseCode);
		}
		
		return response.toString();
		
	}
	
	private String GetPageContent(String url, int ctr) throws Exception {
		
		URL obj = new URL(url);
		conn = (HttpsURLConnection) obj.openConnection();
	 
		conn.setRequestMethod("GET");
	 
		conn.setUseCaches(false);
	 
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
		
		StringBuffer response = new StringBuffer();
		
		if (responseCode == HttpURLConnection.HTTP_OK){
			BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
			String inputLine;
		 
			while ((inputLine = in.readLine()) != null) {
				response.append(inputLine);
			}
			in.close();
		}
		else{
			throw new GoogleLocationHistoryException("~" + ctr + ": " + HTTP_NOTOK_ERROR + responseCode);
		}
	 
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
	
	private static class DelegatingCookieManager extends CookieManager {
		
	    @Override 
	    public void setCookiePolicy(CookiePolicy cookiePolicy) {
	    	controlledCookieManager.get().setCookiePolicy(cookiePolicy);
	    }

	    @Override 
	    public CookieStore getCookieStore() {
	        return controlledCookieManager.get().getCookieStore();
	    }

	    @Override 
	    public Map<String, List<String>> get(URI uri, Map<String, List<String>> requestHeaders) throws IOException {
	        return controlledCookieManager.get().get(uri, requestHeaders);
	    }

	    @Override 
	    public void put(URI uri, Map<String, List<String>> responseHeaders) throws IOException {
	    	controlledCookieManager.get().put(uri, responseHeaders);
	    }
	    
	}
	
	public List<String> getCookies() {
		return cookies;
	}
	
	public void setCookies(List<String> cookies) {
		this.cookies = cookies;
	}

	public String getConnResponse() {
		return connResponse;
	}

	public void setConnResponse(String connResponse) {
		this.connResponse = connResponse;
	}
	
	
	public static void main(String[] args) throws Exception {
		
		String url = "https://accounts.google.com/ServiceLoginAuth";
		 
		GoogleLocationHistory http = new GoogleLocationHistory();
		
		//Turning on Cookies
		CookieHandler.setDefault(new CookieManager());
	 
		//"GET" request, to extract the form's data.
		String basePage = http.GetPageContent(url, 1);
		String postParams = http.getFormParams(basePage, "gestion2@9sistemes.com", "gestion02");
		//String postParams = http.getFormParams(page, "toni.cb@cuxach.es", "1979toni.cb");
	 
		//Construct above post's content and then send a POST request for authentication
		String postResponse = http.sendPost(url, postParams, 2);
		
		//Get KML
		//String result = http.GetPageContent("https://maps.google.co.in/locationhistory/b/0/kml?startTime=1435170600000&endTime=1435257000000");
		String result = http.GetPageContent("https://www.google.co.in/maps/timeline/kml?authuser=0&pb=!1m8!1m3!1i2015!2i5!3i27!2m3!1i2015!2i5!3i27", 4);
		
		System.out.println(result);
		
	}
	 
}
