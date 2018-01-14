package com.locationTracking.xmlManager;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.xpath.XPath;

import com.locationTracking.exceptions.XMLManagerException;


/**
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */
public class XMLParsingManager extends AbstractXMLBaseManager{
	
	private String xmlMessage;
	List<XMLDataBean> alXMLDataBean;
	Map<String, XPath> xPathExpressions;
	
	public XMLParsingManager(String xmlMessage, List<XMLDataBean> alXMLDataBean, boolean validate) throws XMLManagerException{
		
		super.init(xmlMessage, validate);
		this.xmlMessage = xmlMessage;
		this.alXMLDataBean = alXMLDataBean;
	}
	
	public Map<String, String> parseXMLMessage() throws XMLManagerException{
		Map<String, String> hmParsedXMLData = new HashMap<String, String>();
		
		try {
			if (null == document){
				throw new XMLManagerException("Document model is null, cannot parse the XML message.");
			}
			
			xPathExpressions = XMLUtility.processXPathStrings(alXMLDataBean);
			
			for(String name : xPathExpressions.keySet()){
				String parsedValue = getNodeValue(xPathExpressions.get(name), document);
				hmParsedXMLData.put(name, parsedValue);
			}
			
		}
		catch (JDOMException jdomEx) {
			logger.error("JDOMException occurred while parsing the XML Message : " + jdomEx.getMessage());
			throw new XMLManagerException("JDOMException occurred while parsing the XML Message : " + jdomEx.getMessage());
		} 
		catch (Exception ex) {
			logger.error("Exception occurred while parsing the XML Message : " + ex.getMessage());
			throw new XMLManagerException("Exception occurred while parsing the XML Message : " + ex.getMessage());
		}
		
		return hmParsedXMLData;
	}
	
	public Map<String, List<String>> parseXMLMessageForMultipleOccurences() throws XMLManagerException{
		Map<String, List<String>> hmParsedXMLData = new HashMap<String, List<String>>();
		
		try {
			if (null == document){
				throw new XMLManagerException("Document model is null, cannot parse the XML message.");
			}
			
			xPathExpressions = XMLUtility.processXPathStrings(alXMLDataBean);
			
			for(String name : xPathExpressions.keySet()){
				List<String> nodeValuesList = getNodeValueList(xPathExpressions.get(name), document);
				hmParsedXMLData.put(name, nodeValuesList);
			}
			
		}
		catch (JDOMException jdomEx) {
			logger.error("JDOMException occurred while parsing the XML Message : " + jdomEx.getMessage());
			throw new XMLManagerException("JDOMException occurred while parsing the XML Message : " + jdomEx.getMessage());
		} 
		catch (Exception ex) {
			logger.error("Exception occurred while parsing the XML Message : " + ex.getMessage());
			throw new XMLManagerException("Exception occurred while parsing the XML Message : " + ex.getMessage());
		}
		
		return hmParsedXMLData;
		
	}
	
	public String getNodeValue(XPath xPathExpr, Object object) {
		
		String value = null;

		try {
			if (null != xPathExpr) {
				value = xPathExpr.valueOf(object);
			}
	    }
		catch (JDOMException jdomEx) {
			logger.error("JDOMException occurred while evaluating XPath expression : " + jdomEx.getMessage());
			throw new XMLManagerException("JDOMException occurred while evaluating XPath expression : " + jdomEx.getMessage());
		}
		
		return value;
	}
	
	public List<String> getNodeValueList(XPath xPathExpr, Object object) {
		
		List<String> nodeValuesList = null;

		try {
			if (null != xPathExpr) {
				
				List<Element> elementList = xPathExpr.selectNodes(object);
				
				nodeValuesList = new ArrayList<String>();
				
				for (Element element : elementList){
					String value = element.getValue();
					nodeValuesList.add(value);
				}
			}
	    }
		catch (JDOMException jdomEx) {
			logger.error("JDOMException occurred while evaluating XPath expression : " + jdomEx.getMessage());
			throw new XMLManagerException("JDOMException occurred while evaluating XPath expression : " + jdomEx.getMessage());
		}
		
		return nodeValuesList;
	}

}
