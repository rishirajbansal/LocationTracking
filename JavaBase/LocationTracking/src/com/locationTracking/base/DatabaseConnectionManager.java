/**
 * 
 */
package com.locationTracking.base;

import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.Properties;
import java.util.ResourceBundle;

import javax.sql.DataSource;

import org.apache.commons.dbcp.ConnectionFactory;
import org.apache.commons.dbcp.DriverManagerConnectionFactory;
import org.apache.commons.dbcp.PoolableConnectionFactory;
import org.apache.commons.dbcp.PoolingDataSource;
import org.apache.commons.pool.impl.GenericObjectPool;

import com.locationTracking.exceptions.DatabaseConnectionManagerException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;


/**
 * @author Rishi
 *
 */
public class DatabaseConnectionManager {
	
	public static LoggerManager logger = Utility.getLogger(DatabaseConnectionManager.class.getName());
	
	private String driver;
	private String url;
	private String username;
	private String password;
	private String autoCommit;
	private String maxActivePoolSize;
	
	private static DataSource dataSource;
	
	private static DatabaseConnectionManager dbConManager = null;
	
	/** Object used for locking. */
	private static Object lockObject = new Object();
	
	{
		ResourceBundle rBundle = ResourceBundle.getBundle(Constants.DATABASE_PROPERTIES_FILE_NAME);
		
		driver = rBundle.getString(Constants.DATABASE_DRIVER);
		url = rBundle.getString(Constants.DATABASE_URL);
		username = rBundle.getString(Constants.DATABASE_USERNAME);
		password = rBundle.getString(Constants.DATABASE_PASSWORD);
		autoCommit = rBundle.getString(Constants.DATABASE_AUTO_COMMIT);
		maxActivePoolSize = rBundle.getString(Constants.DATABASE_ACTIVE_MAX_POOL_SIZE);
		
	}
	
	
	private void setupDataSource() throws DatabaseConnectionManagerException{
		
		try{
			logger.info("Loading the database driver...");
			Class.forName(driver);
			
			logger.info("Configuring the connection factory...");
			ConnectionFactory conFactory = new DriverManagerConnectionFactory(url, username, password);
			
			GenericObjectPool connectionPool = new GenericObjectPool();
			connectionPool.setMaxActive(Integer.parseInt(maxActivePoolSize));
			
			PoolableConnectionFactory poolConFactory = new PoolableConnectionFactory(conFactory, connectionPool, null, null, false, Boolean.parseBoolean(autoCommit));
			
			logger.info("Creating datasource...");
			dataSource = new PoolingDataSource(connectionPool);
		}
		catch(ClassNotFoundException cnfEx){
			logger.error(" ClassNotFoundException occured during setup Data source : " + cnfEx.getMessage());
			throw new DatabaseConnectionManagerException("ClassNotFoundException occured duting setup Data source ", cnfEx);
		}
		catch(Exception ex){
			logger.error(" Exception occured during setup Data source : " + ex.getMessage());
			throw new DatabaseConnectionManagerException("Exception occured duting setup Data source ", ex);
		}
		
	}
	
	public static synchronized Connection getConnection() throws SQLException{
		return dataSource.getConnection();
	}
	
	public static void instantiate() throws DatabaseConnectionManagerException{
		try{
			if (null == dbConManager) {
				synchronized(lockObject){
					dbConManager = new DatabaseConnectionManager();
					dbConManager.setupDataSource();
				}
			}
		}
		catch(Exception ex){
			logger.error(" Exception occured in instantiating : " + ex.getMessage());
			throw new DatabaseConnectionManagerException("Exception occured in in instantiatin ", ex);
		}
		catch(Throwable th){
			logger.error(" Throwable occured in instantiating : " + th.getMessage());
			throw new DatabaseConnectionManagerException("Throwable occured in instantiatin ", th);
		}
	}
	
	public static synchronized void returnConnection(Connection con) throws DatabaseConnectionManagerException{
		try{
			if (null != con){
				con.close();
			}
		}
		catch(SQLException sqlEx){
			logger.error(" SQLException occured in  returnConnection  : " + sqlEx.getMessage());
			throw new DatabaseConnectionManagerException("SQLException occured in returnConnection ", sqlEx);
		}
	}
	
	public static synchronized void clearResources(Statement stmt, ResultSet rs) throws DatabaseConnectionManagerException{
		try{
			if (null != stmt){
				stmt.close();
				stmt = null;
			}
			if (null != rs){
				rs.close();
				rs = null;
			}
		}
		catch(SQLException sqlEx){
			logger.error(" SQLException occured in clearResources  : " + sqlEx.getMessage());
			throw new DatabaseConnectionManagerException("SQLException occured in clearResources ", sqlEx);
		}
	}
	
	public static synchronized void clearResources(Statement... stmts) throws DatabaseConnectionManagerException{
		try{
			for (Statement stmt : stmts){
				if (null != stmt){
					stmt.close();
					stmt = null;
				}
			}
		}
		catch(SQLException sqlEx){
			logger.error(" SQLException occured in clearResources  : " + sqlEx.getMessage());
			throw new DatabaseConnectionManagerException("SQLException occured in clearResources ", sqlEx);
		}
	}
	
	public static synchronized void clearResources(ResultSet... sets) throws DatabaseConnectionManagerException{
		try{
			for (ResultSet rs : sets){
				if (null != rs){
					rs.close();
					rs = null;
				}
			}
		}
		catch(SQLException sqlEx){
			logger.error(" SQLException occured in clearResources  : " + sqlEx.getMessage());
			throw new DatabaseConnectionManagerException("SQLException occured in clearResources ", sqlEx);
		}
	}
	
	public static boolean testDBConnection() throws DatabaseConnectionManagerException{
		Connection con = null;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			DatabaseMetaData metaData = con.getMetaData();
			
			if (null != metaData){
				logger.info("Database product Name: " + metaData.getDatabaseProductName());
				logger.info("Database product Version: " + metaData.getDatabaseProductVersion());
				
				logger.info("Test DB connection is successful.");
				
				return true;
			}
			else{
				logger.error("Test DB connection failed.");
				
				return false;
			}
			
		}
		catch(SQLException sqlEx){
			logger.error(" SQLException occured in testDBConnection  : " + sqlEx.getMessage());
			return false;
		}
		finally{
			DatabaseConnectionManager.returnConnection(con);
		}
	}

}
