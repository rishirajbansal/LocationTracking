package com.locationTracking.xmlManager;

/**
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.jdom.JDOMException;
import org.jdom.xpath.XPath;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.generic.Utility;



public class XMLUtility {
	
	public static LoggerManager logger = Utility.getLogger(XMLUtility.class.getName());
	
	
	public static Map<String, XPath> processXPathStrings(List<XMLDataBean> alXMLDataBean) throws JDOMException{
		
		String entityName   = null;
		String entityValue  = null;
		Map<String, XPath> xPathExpressions = new HashMap<String, XPath>();		
		
		
		for (XMLDataBean xmlDataBean : alXMLDataBean) {
			entityName = xmlDataBean.getName();
			entityValue = xmlDataBean.getXPath();
			xPathExpressions.put(entityName, processXPathExpression(entityValue));
		}
		
		return xPathExpressions;
	}
	
	public static XPath processXPathExpression(String xPathExpr) throws JDOMException{
		
		XPath xPath = null;
		
		xPath = XPath.newInstance(xPathExpr);
		
		return xPath;
	} 

}
 