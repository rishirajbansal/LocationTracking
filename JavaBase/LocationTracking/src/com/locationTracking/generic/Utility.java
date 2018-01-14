/**
 * 
 */
package com.locationTracking.generic;

import java.io.IOException;

import sun.misc.BASE64Decoder;

import com.locationTracking.base.LoggerManager;


/**
 * @author Rishi
 *
 */
public class Utility {
	
	public static LoggerManager getLogger(String className) {
		return new LoggerManager(className);
	}
	
	/**
	 * safeTrim takes a String and trims the leading and trailing spaces and returs a it
	 * this method will return an empty string if the String passed is is ==null or is the string "null"
	 *
	 * @param s Sting string to trim leading and trailing spaces
	 * @return String
	 */
	public static String safeTrim(String s) {
		if ((s == null) || s.equals("null")) {
			return "";
		}
		else {
			return s.trim();
		}
	}
	
	public static String base64Decode(String encodedString) throws IOException{
		
		BASE64Decoder decode = new BASE64Decoder();
		byte[] decodeArr = decode.decodeBuffer(encodedString);
		String dencodedString = new String(decodeArr);

		return dencodedString;
		
	}

}
