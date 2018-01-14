package com.locationTracking.xmlManager;

public class XMLDataBean {
	
	
	public enum XMLElementTypes {
		
		XML_ELEMENT_TYPE_ROOT,
		XML_ELEMENT_TYPE_ELEMENT,
		XML_ELEMENT_TYPE_ATTRIBUTE,
		XML_ELEMENT_TYPE_NAMESPACE,
		XML_ELEMENT_TYPE_COMMENT,
		XML_ELEMENT_TYPE_FPML_ELEMENT,
		XML_ELEMENT_TYPE_DYNAMIC_ELEMENT;
		
	}
	
	private String XPath;
	
	private String name;
	
	private XMLElementTypes type;
	
	private String value;
	

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public XMLElementTypes getType() {
		return type;
	}

	public void setType(XMLElementTypes type) {
		this.type = type;
	}

	public String getValue() {
		return value;
	}

	public void setValue(String value) {
		this.value = value;
	}

	public String getXPath() {
		return XPath;
	}

	public void setXPath(String path) {
		XPath = path;
	}
	
	


}
