package com.locationTracking.xmlManager;

import java.io.IOException;
import java.io.StringReader;

import org.jdom.Document;
import org.jdom.JDOMException;
import org.jdom.input.DOMBuilder;
import org.jdom.input.SAXBuilder;
import org.jdom.output.Format;
import org.jdom.output.XMLOutputter;

import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.XMLManagerException;
import com.locationTracking.generic.Utility;


/**
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */
public abstract class AbstractXMLBaseManager {
	
	public static LoggerManager logger = Utility.getLogger(AbstractXMLBaseManager.class.getName());
	
	protected DOMBuilder jdomBuilder = new DOMBuilder();
	
	XMLOutputter xmlOutput = new XMLOutputter();
	
	protected Document document;
	
	public AbstractXMLBaseManager(){
		
	}
	
	public void init(String XMLMessage, boolean validate) throws XMLManagerException{
		
		StringReader stringReader = new StringReader(XMLMessage);
		
		SAXBuilder saxBuilder = new SAXBuilder();
		saxBuilder.setFeature("http://apache.org/xml/features/validation/schema", validate);
		
		try {
			document = saxBuilder.build(stringReader);
		} 
		catch (JDOMException jdomEx) {
			logger.error("JDOMException occurred while building the JDOM document from the given XML : " + jdomEx.getMessage());
			throw new XMLManagerException("JDOMException occurred while building the JDOM document from the given XML : " + jdomEx.getMessage());
		} 
		catch (IOException ioEx) {
			logger.error("IOException occurred while building the JDOM document from the given XML : " + ioEx.getMessage());
			throw new XMLManagerException("IOException occurred while building the JDOM document from the given XML : " + ioEx.getMessage());
		}
	}
	
	public String getXMLOutputString(Document document) throws XMLManagerException{
		
		String xmlOutputString = "";
		
		try{
			Format format = Format.getRawFormat();
			format.setIndent("  ");
			//format.setLineSeparator(System.getProperty("line.separator"));
			
			xmlOutput.setFormat(format);
			xmlOutputString = xmlOutput.outputString(document);
		}
		catch (Exception jdomEx) {
			logger.error("JDOMException occurred while creating the XML from JDOM Document : " + jdomEx.getMessage());
			throw new XMLManagerException("JDOMException occurred while creating the XML from JDOM Document : " + jdomEx.getMessage());
		}
		
		return xmlOutputString;
		
	}

}
