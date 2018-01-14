package com.locationTracking.selenium;

import java.io.File;
import java.io.IOException;
import java.nio.file.FileSystems;
import java.util.HashMap;
import java.util.logging.Level;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.logging.LogType;
import org.openqa.selenium.logging.LoggingPreferences;
import org.openqa.selenium.remote.CapabilityType;
import org.openqa.selenium.remote.DesiredCapabilities;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.RetrieveLocationHistoryException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;
import com.thoughtworks.selenium.Selenium;
import com.thoughtworks.selenium.webdriven.WebDriverBackedSelenium;

/**
 * Login to Google account and download location history KML file
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */

public class RetrieveLocationHistory {
	
	private static final long serialVersionUID = 1L;

	public static LoggerManager logger = Utility.getLogger(RetrieveLocationHistory.class.getName());
	
	@SuppressWarnings("deprecation")
	public boolean getLocationHistory(String userid, String password, String downloadFilepath, String startTime, String endTime){
		
		boolean flag = true;
		
		try{
			if (!Utility.safeTrim(userid).equals(Constants.EMPTY_STRING) && !Utility.safeTrim(password).equals(Constants.EMPTY_STRING) && !Utility.safeTrim(downloadFilepath).equals(Constants.EMPTY_STRING)){
				
				String locationHistoryURL = "https://maps.google.co.in/locationhistory/b/0/kml?startTime=" + startTime + "&endTime=" + endTime;
				logger.debug(locationHistoryURL);
				String chromedriverPath = System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "chromedriver.exe";
				logger.debug("chromedriverPath : " + chromedriverPath);
				System.setProperty("webdriver.chrome.driver", chromedriverPath);

				LoggingPreferences logs = new LoggingPreferences();
				logs.enable(LogType.DRIVER, Level.SEVERE);
				logs.enable(LogType.BROWSER, Level.SEVERE);
				
				HashMap<String, Object> chromePrefs = new HashMap<String, Object>();
			    chromePrefs.put("profile.default_content_settings.popups", 0);
			    chromePrefs.put("download.default_directory", downloadFilepath);

			    ChromeOptions chromeOptions = new ChromeOptions();
			    chromeOptions.setExperimentalOption("prefs", chromePrefs);
			    
			    DesiredCapabilities cap = DesiredCapabilities.chrome();
			    cap.setCapability(CapabilityType.ACCEPT_SSL_CERTS, true);
			    cap.setCapability(ChromeOptions.CAPABILITY, chromeOptions);
			    cap.setCapability(CapabilityType.LOGGING_PREFS, logs);
			    
			    WebDriver driver = new ChromeDriver(cap);
				
				String baseUrl = "https://accounts.google.com/";
				
				Selenium selenium = new WebDriverBackedSelenium(driver, baseUrl);
				selenium.open("/ServiceLogin?service=friendview&passive=true&rm=false");
				selenium.type("id=Email", userid);
				
				/*The google auto login stopped working, so changing the logic*/
				//selenium.type("id=Passwd", password);
				selenium.click("id=next");
				
				selenium.type("id=Passwd-hidden", password);
				//selenium.type("id=Passwd", password);
				selenium.waitForPageToLoad("10000");
				/*The google auto login stopped working, so changing the logic*/
				
				selenium.click("id=signIn");
				//selenium.waitForPageToLoad("1000");
				selenium.waitForPageToLoad("20000");
				
				selenium.open(locationHistoryURL);
				selenium.waitForPageToLoad("5000");

				WebDriver driverInstance = ((WebDriverBackedSelenium) selenium).getWrappedDriver();
				//Commenting as it was displaying unnecessary errors on logs
				/*driverInstance.close();
				driverInstance.quit();*/
				
				Runtime.getRuntime().exec("taskkill /t /f /im chromedriver.exe");
				Thread.sleep(500);
				
				/*driverInstance.close();
				driverInstance.quit();*/
				
			}
			else{
				logger.error("User id or password or download path is empty");
				throw new RetrieveLocationHistoryException("User id or password or download path is empty");
			}
			
		}
		catch(RetrieveLocationHistoryException rlEx){
			throw rlEx;
		}
		catch (RuntimeException rRx) 
	    {
	        logger.warn("RuntimeException - Ignorable due to Selenium :" + rRx.toString());
	        try {
				Runtime.getRuntime().exec("taskkill /t /f /im chromedriver.exe");
				Thread.sleep(500);
			} 
	        catch (Exception ex){
				logger.error("Exception occured while getting location history via Selenium from Google" + ex.getMessage());
				throw new RetrieveLocationHistoryException("Exception occured while getting location history via Selenium from Google" + ex.getMessage());
			}
			
	    }
		catch (Exception ex){
			logger.error("Exception occured while getting location history via Selenium from Google" + ex.getMessage());
			throw new RetrieveLocationHistoryException("Exception occured while getting location history via Selenium from Google" + ex.getMessage());
		}
		catch(Throwable th){
			logger.error("Throwable occurred : " + th.getMessage());
			throw new RetrieveLocationHistoryException("Throwable occurred : " + th.getMessage());
		}
		
		return flag;
	}
	
	public static void main(String[] args) throws IOException{
		
		File downloadDir = new File(System.getProperty("user.dir") + FileSystems.getDefault().getSeparator() + "kml");
		boolean dirCreated = downloadDir.mkdir();
		
	    String downloadFilepath = downloadDir.getCanonicalPath();
	    
	    System.out.println(downloadFilepath);
		
		RetrieveLocationHistory rl = new RetrieveLocationHistory();
		boolean res = rl.getLocationHistory("gestion2@9sistemes.com", "gestion02", downloadFilepath, "1435170600000", "1435257000000");
		
		System.out.println(res);
		
	}

}
