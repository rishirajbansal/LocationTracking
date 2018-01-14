/**
 * 
 */
package com.locationTracking.exceptions;

import com.locationTracking.generic.Constants;


/**
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */
@SuppressWarnings("serial")
public class ApplicationException extends RuntimeException{
	
	protected Throwable throwable = null;
	
	String error = "";
	
	/**
     * Constructor for ApplicationException
     * @param msg - Message associated with the exception
     */
    public ApplicationException(String msg) {
    	super(msg);
    	error = msg;
    }

    /**
     * Initializes a newly created <code>ApplicationException</code> object.
     * @param	msg - the message associated with the Exception.
     * @param   cause - Throwable object
     */
    public ApplicationException(String msg, Throwable cause) {
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
    	if (throwable == null) {
    		return Constants.EMPTY_STRING;
        }
    	
    	return error;
    }
    
  
}
