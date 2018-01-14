package com.locationTracking.exceptions;

/**
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */

@SuppressWarnings("serial")

public class BusinessEngineException extends ApplicationException{
	
	protected Throwable throwable = null;
	
	String error = "";
	
	/**
     * Constructor for BusinessEngineException
     * @param msg - Message associated with the exception
     */
    public BusinessEngineException(String msg) {
    	super(msg);
    	error = msg;
    }

    /**
     * Initializes a newly created <code>BusinessEngineException</code> object.
     * @param	msg - the message associated with the Exception.
     * @param   cause - Throwable object
     */
    public BusinessEngineException(String msg, Throwable cause) {
    	super(msg, cause);
    }
    
    
    /**
     * Returns the error message string of the exception object.
     *
     * @return  the error message string of this <code>Exception</code>
     *          object if it was <code>Exception</code> with an
     *          error message string; or <code>null</code> if it was
     *          <code>Exception</code> created} with no error message.
     *
     */
    public String getMessage() {
    	    	
        return error;
    }

}
