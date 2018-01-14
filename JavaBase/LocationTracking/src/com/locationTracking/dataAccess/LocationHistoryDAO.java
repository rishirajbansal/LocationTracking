package com.locationTracking.dataAccess;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import com.locationTracking.base.DatabaseConnectionManager;
import com.locationTracking.base.LoggerManager;
import com.locationTracking.exceptions.DataAccessException;
import com.locationTracking.exceptions.DatabaseConnectionManagerException;
import com.locationTracking.generic.Constants;
import com.locationTracking.generic.Utility;

/**
 * DAO class to interact with database
 * @author Rishi Raj Bansal
 * @since June 2015 
 *
 */

public class LocationHistoryDAO {
	
	public static LoggerManager logger = Utility.getLogger(LocationHistoryDAO.class.getName());
	
	
	private static final String SQL_FETCH_WORKERS = "SELECT * FROM workers WHERE status = ?";
	private static final String SQL_SELECT_IF_EXISTS_HISTORY = "SELECT * FROM historyexecution_00 WHERE lastexecuted = CURRENT_DATE() AND idworkers = ?";
	private static final String SQL_DELETE_EXISTING_LOCATION_HISTORY = "DELETE FROM locationhistory_00 WHERE idhistoryexecution = ?";
	private static final String SQL_DELETE_EXISTING_EXECUTION_HISTORY = "DELETE FROM historyexecution_00 WHERE lastexecuted = CURRENT_DATE() AND idworkers = ?";
	private static final String SQL_INSERT_EXECUTION_HISTORY = "INSERT INTO historyexecution_00 (idworkers, lastexecuted, created_on) VALUES (?, CURRENT_DATE(), now())";
	private static final String SQL_INSERT_LOCATION_HISTORY = "INSERT INTO locationhistory_00 (idworkers, idhistoryexecution, timestamp, latitude, longitude, fmt_timestamp, location, apptimestamp) VALUES ";
	private static final String SQL_LOAD_LOCATIONS = "SELECT * FROM geolocations";
	private static final String SQL_INSERT_LOCATION = "INSERT INTO geolocations (latitude, longitude, location, created_on) VALUES(?, ?, ?, NOW())";
	
	private static final String SQL_FETCH_MANUALRETRIEVAL = "SELECT * FROM manualretrieval, workers WHERE manualretrieval.idworkers = workers.idworkers AND manualretrieval.status = ? AND workers.status = ?";
	private static final String SQL_INSERT_EXECUTION_HISTORY_FOR_MANUALRETRIEVAL = "INSERT INTO historyexecution_00 (idworkers, lastexecuted, created_on) VALUES (?, ?, now())";
	private static final String SQL_SELECT_IF_EXISTS_HISTORY_FOR_MANUALRETRIEVAL = "SELECT * FROM historyexecution_00 WHERE lastexecuted = ? AND idworkers = ?";
	private static final String SQL_DELETE_EXISTING_EXECUTION_HISTORY_FOR_MANUALRETRIEVAL = "DELETE FROM historyexecution_00 WHERE lastexecuted = ? AND idworkers = ?";
	private static final String SQL_UPDATE_MANUALRETRIEVAL = "UPDATE manualretrieval SET status = ?, processedon = NOW(), comments = ? WHERE idmanualretrieval = ?";
	
	private static final String SQL_SELECT_IF_EXISTS_LATEST_LOCATION_HISTORY = "SELECT * FROM latestlocationhistory WHERE idworkers = ?";
	private static final String SQL_DELETE_EXISTING_LATEST_LOCATION_HISTORY = "DELETE FROM latestlocationhistory WHERE idworkers = ?";
	private static final String SQL_INSERT_LATEST_LOCATION_HISTORY = "INSERT INTO latestlocationhistory (iddomains, idworkers, latitude, longitude, fmt_timestamp, location, apptimestamp) VALUES (?, ?, ?, ?, ?, ?, ?)";
	
	
	public List<Worker> fetchAllWorkers() throws DataAccessException{
		
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		List<Worker> workers = new ArrayList<Worker>();
		
		try{
			con = DatabaseConnectionManager.getConnection();
			ps = con.prepareStatement(SQL_FETCH_WORKERS);
			ps.setInt(1, Constants.WORKER_STATUS_ACTIVE);
			
			rs = ps.executeQuery();
			
			logger.debug("[fetchAllWorkers()-QUERY] : " + ps.toString());
			
			while (rs.next()){
				
				Worker worker = new Worker();
				worker.setWorkerId(rs.getInt("idworkers"));
				worker.setDomainId(rs.getInt("iddomains"));
				worker.setName(rs.getString("name"));
				worker.setEmail(rs.getString("email"));
				worker.setPassword(rs.getString("password"));
				worker.setDecodedPassword(Utility.base64Decode(rs.getString("password")));
				
				workers.add(worker);

			}
			
		}
		catch(IOException ioEx){
			logger.error("fetchAllWorkers", "IOException occurred decoding the password in DAO layer : " + ioEx.getMessage());
			throw new DataAccessException("fetchAllWorkers() -> IOException occurred decoding the password in DAO layer : " + ioEx.getMessage());
		}
		catch(SQLException sqlEx){
			logger.error("fetchAllWorkers", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("fetchAllWorkers() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("fetchAllWorkers", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("fetchAllWorkers() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps);
				DatabaseConnectionManager.clearResources(rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("fetchAllWorkers() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return workers;
	
	}
	
	/*public boolean ifWokerExist(Worker worker) throws DataAccessException{
		
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		ResultSet generatedKeys = null;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			ps = con.prepareStatement(SQL_SELECT_FIND_WORKER);
			ps.setString(1, worker.getName());
			
			rs = ps.executeQuery();
			
			logger.debug("[ifWokerExist()-QUERY] : " + ps.toString());
			
			if (rs.next()){
				logger.debug("Worker exists in the database");
				
				int idworker = rs.getInt("idworkers");
				worker.setIdworkers(idworker);
			}
			else{
				logger.debug("Worker does not exist in the database, need to insert");
				
				 Insert the worker details in database 
				ps = con.prepareStatement(SQL_INSERT_WORKER, Statement.RETURN_GENERATED_KEYS);
				ps.setString(1, worker.getName());
				ps.setString(2, worker.getEmail());
				
				logger.debug("[ifWokerExist()-QUERY 2] : " + ps.toString());
				
				int rowsInserted = ps.executeUpdate();
				
				if (rowsInserted <= 0){
					throw new DataAccessException("ifWokerExist() -> Failed to insert record for worker's in database.");
				}
				generatedKeys = ps.getGeneratedKeys();
				if (generatedKeys.next()){
					worker.setIdworkers(generatedKeys.getInt(1));
					logger.info("New worker found and inserted, id: " + worker.getIdworkers());
					con.commit();
				}
				else{
					throw new DataAccessException("ifWokerExist() -> Failed to insert record for worker's in database, no generated key obtained.");
				}
			}
		}
		catch(SQLException sqlEx){
			logger.error("ifWokerExist", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("ifWokerExist() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("ifWokerExist", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("ifWokerExist() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps, generatedKeys);
				DatabaseConnectionManager.clearResources(rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("ifWokerExist() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return flag;
	
	}*/
	
	public boolean deletePreviousHistory(Worker worker) throws DataAccessException{
		
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		int idHistoryExecution = 0;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			/*Check if previous history records exists for that date*/
			String sql = SQL_SELECT_IF_EXISTS_HISTORY.replaceAll("00", Integer.toString(worker.getDomainId()));
			ps = con.prepareStatement(sql);
			ps.setInt(1, worker.getWorkerId());
			rs = ps.executeQuery();
			
			logger.debug("[deletePreviousHistory()-QUERY 1] : " + ps.toString());
			
			if (rs.next()){
				logger.debug("Record already exists for the date, it will be deleted first.");
				idHistoryExecution = rs.getInt("idhistoryexecution");
				
				//Delete location history and then history execution due to the foreign key relationship
				DatabaseConnectionManager.clearResources(ps);
				sql = SQL_DELETE_EXISTING_LOCATION_HISTORY.replaceAll("00", Integer.toString(worker.getDomainId()));
				ps = con.prepareStatement(sql);
				ps.setInt(1, idHistoryExecution);
				logger.debug("[deletePreviousHistory()-QUERY 2] : " + ps.toString());
				
				int rowsUpdated = ps.executeUpdate();
				
				if (rowsUpdated <= 0){
					logger.debug("No record exists in location history for this date");
				}
				else{
					logger.debug("Records found in the location history and deleted");
				}
				
				sql = SQL_DELETE_EXISTING_EXECUTION_HISTORY.replaceAll("00", Integer.toString(worker.getDomainId()));
				ps = con.prepareStatement(sql);
				ps.setInt(1, worker.getWorkerId());
				
				logger.debug("[deletePreviousHistory()-QUERY 3] : " + ps.toString());
				
				rowsUpdated = ps.executeUpdate();
				
				if (rowsUpdated <= 0){
					throw new DataAccessException("deletePreviousHistory() -> Failed to delete record for history execution in database.");
				}
				else{
					con.commit();
				}
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("deletePreviousHistory", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("deletePreviousHistory() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("deletePreviousHistory", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("deletePreviousHistory() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps, rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("deletePreviousHistory() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return flag;
		
	}
	
	public int saveHistoryExecution(Worker worker) throws DataAccessException{
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		int idHistoryExecution = 0;
		ResultSet generatedKeys = null;;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			String sql = SQL_INSERT_EXECUTION_HISTORY.replaceAll("00", Integer.toString(worker.getDomainId()));
			ps = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
			ps.setInt(1, worker.getWorkerId());
			
			logger.debug("[saveHistoryExecution()-QUERY 1] : " + ps.toString());
			
			int rowsInserted = ps.executeUpdate();
			
			if (rowsInserted <= 0){
				throw new DataAccessException("saveHistoryExecution() -> Failed to insert record for history execution in database.");
			}
			generatedKeys = ps.getGeneratedKeys();
			if (generatedKeys.next()){
				idHistoryExecution = generatedKeys.getInt(1);
				logger.info("New history execution inserted, id: " + idHistoryExecution);
				con.commit();
			}
			else{
				throw new DataAccessException("saveHistoryExecution() -> Failed to insert record for history execution in database, no generated key obtained.");
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("saveHistoryExecution", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("saveHistoryExecution() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("saveHistoryExecution", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("saveHistoryExecution() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps, rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("saveHistoryExecution() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return idHistoryExecution;
		
	}
	
	public boolean saveLocationHistory(List<LocationHistory> alLocationHistory, Worker worker) throws DataAccessException{
		
		boolean flag = true;
		
		Connection con = null;
		Statement stmt = null;
		String sql = "";
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			/*Insert location history*/
			stmt = con.createStatement();
			stmt.clearBatch();
			int rowsCount = 0;
			
			String insertSQL = SQL_INSERT_LOCATION_HISTORY.replaceAll("00", Integer.toString(worker.getDomainId()));
			
			for (LocationHistory object : alLocationHistory){
				sql = insertSQL + "(" + object.getIdworkers() + "," + object.getIdHistoryExecution() + ",'" + 
								object.getTimestampString() + "','" + object.getLatitude() + "','" + object.getLongitude() + "','" + 
								object.getFormattedTimestamp() + "','" + object.getLocation() + "','" + object.getApplicationBasedTimestamp() + "')";
				
				//logger.debug("[saveLocationHistory ()-QUERY of batch : " + rowsCount + "] : " + sql);
				
				stmt.addBatch(sql);
				
				++rowsCount;
			}
			
			//Execute the Batch in DB, if founds any records from above 
			if (rowsCount > 0){
				int sum = 0;
				
				int rows[] = stmt.executeBatch();
				int n = rows.length;
				for (int i=0; i < n; i++){
					sum += rows[i];
				}
				
				if (sum == rowsCount){
					logger.debug("Batch executed succesfully.");
					con.commit();
					flag = true;
				}
				else{
					logger.error("Batch not executed succesfully.");
					con.rollback();
					throw new DataAccessException("Batch of location history not executed successfully");
				}
			}
			else{
				logger.debug("No rows found in the batch to be executed.");
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("saveLocationHistory", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("saveLocationHistory() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("saveLocationHistory", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("saveLocationHistory() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(stmt);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("saveLocationHistory() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return flag;
		
	}
	
	public boolean saveLatestLocationHistory(LocationHistory locationHistory, Worker worker) throws DataAccessException{
		
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			/*Check if previous latest location history records exists for that worker*/
			ps = con.prepareStatement(SQL_SELECT_IF_EXISTS_LATEST_LOCATION_HISTORY);
			ps.setInt(1, worker.getWorkerId());
			rs = ps.executeQuery();
			
			logger.debug("[saveLatestLocationHistory()-QUERY 1] : " + ps.toString());
			
			if (rs.next()){
				logger.debug("Record already exists for the latest Location history, it will be deleted first.");
				
				//Delete latest location history
				DatabaseConnectionManager.clearResources(ps);
				ps = con.prepareStatement(SQL_DELETE_EXISTING_LATEST_LOCATION_HISTORY);
				ps.setInt(1, worker.getWorkerId());
				logger.debug("[saveLatestLocationHistory()-QUERY 2] : " + ps.toString());
				
				int rowsUpdated = ps.executeUpdate();
				
				if (rowsUpdated <= 0){
					logger.debug("No record exists in latest location history for this date");
				}
				else{
					logger.debug("Records found in the latest location history and deleted");
					con.commit();
				}
			}

			DatabaseConnectionManager.clearResources(ps);
			ps = con.prepareStatement(SQL_INSERT_LATEST_LOCATION_HISTORY);
			ps.setInt(1, worker.getDomainId());
			ps.setInt(2, locationHistory.getIdworkers());
			ps.setString(3, locationHistory.getLatitude());
			ps.setString(4, locationHistory.getLongitude());
			ps.setString(5, locationHistory.getFormattedTimestamp());
			ps.setString(6, locationHistory.getLocation());
			ps.setString(7, locationHistory.getApplicationBasedTimestamp());
			
			logger.debug("[saveLatestLocationHistory()-QUERY 3] : " + ps.toString());
			
			int rowsInserted = ps.executeUpdate();
			
			if (rowsInserted <= 0){
				throw new DataAccessException("saveLatestLocationHistory() -> Failed to insert record for latest location history in database.");
			}
			else{
				con.commit();
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("deletePreviousHistory", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("deletePreviousHistory() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("deletePreviousHistory", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("deletePreviousHistory() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps, rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("deletePreviousHistory() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return flag;
		
	}
	
	public Map<String, String> fetchCachedLocations() throws DataAccessException{
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		Map<String, String> cachedLocations = new HashMap<String, String>();
		
		try{
			con = DatabaseConnectionManager.getConnection();
			ps = con.prepareStatement(SQL_LOAD_LOCATIONS);
			
			rs = ps.executeQuery();
			
			//logger.debug("[fetchCachedLocations()-QUERY] : " + ps.toString());
			
			while (rs.next()){
				
				String latitude = rs.getString("latitude");
				String longitude = rs.getString("longitude");
				String location = rs.getString("location");
				
				cachedLocations.put(latitude + "|" + longitude , location);
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("fetchCachedLocations", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("fetchCachedLocations() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("fetchCachedLocations", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("fetchCachedLocations() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("fetchCachedLocations() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		
		return cachedLocations;
	
	}
	
	public boolean saveLocation(String latitude, String longitude, String location) throws DataAccessException{
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			ps = con.prepareStatement(SQL_INSERT_LOCATION);
			ps.setString(1, latitude);
			ps.setString(2, longitude);
			ps.setString(3, location);
			
			//logger.debug("[saveLocation()-QUERY] : " + ps.toString());
			
			int rowsInserted = ps.executeUpdate();
			
			if (rowsInserted <= 0){
				throw new DataAccessException("saveLocation() -> Failed to insert record for new location in database.");
			}
			else{
				con.commit();
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("fetchCachedLocations", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("fetchCachedLocations() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("fetchCachedLocations", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("fetchCachedLocations() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("fetchCachedLocations() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		
		return flag;
	
	}
	
	public List<Worker> fetchManualRetrievalRequests() throws DataAccessException{
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		List<Worker> workers = new ArrayList<Worker>();
		
		try{
			con = DatabaseConnectionManager.getConnection();
			ps = con.prepareStatement(SQL_FETCH_MANUALRETRIEVAL);
			ps.setInt(1, Constants.MANUALRETRIEVALREQUESTS_STATUS_ACTIVE);
			ps.setInt(2, Constants.WORKER_STATUS_ACTIVE);
			
			rs = ps.executeQuery();
			
			logger.debug("[fetchManualRetrievalRequests()-QUERY] : " + ps.toString());
			
			while (rs.next()){
				
				Worker worker = new Worker();
				worker.setWorkerId(rs.getInt("idworkers"));
				worker.setDomainId(rs.getInt("iddomains"));
				worker.setName(rs.getString("name"));
				worker.setEmail(rs.getString("email"));
				worker.setPassword(rs.getString("password"));
				worker.setDecodedPassword(Utility.base64Decode(rs.getString("password")));
				
				worker.setManualRetrievalId(rs.getInt("idmanualretrieval"));
				worker.setRequestedRecordDate(rs.getString("requested_recorddate"));
				
				workers.add(worker);

			}
			
		}
		catch(IOException ioEx){
			logger.error("fetchManualRetrievalRequests", "IOException occurred decoding the password in DAO layer : " + ioEx.getMessage());
			throw new DataAccessException("fetchManualRetrievalRequests() -> IOException occurred decoding the password in DAO layer : " + ioEx.getMessage());
		}
		catch(SQLException sqlEx){
			logger.error("fetchManualRetrievalRequests", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("fetchManualRetrievalRequests() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("fetchManualRetrievalRequests", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("fetchManualRetrievalRequests() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps);
				DatabaseConnectionManager.clearResources(rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("fetchManualRetrievalRequests() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return workers;
	
	}
	
	public int saveHistoryExecutionForManualRetrieval(Worker worker) throws DataAccessException{
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		int idHistoryExecution = 0;
		ResultSet generatedKeys = null;;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			String sql = SQL_INSERT_EXECUTION_HISTORY_FOR_MANUALRETRIEVAL.replaceAll("00", Integer.toString(worker.getDomainId()));
			ps = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
			ps.setInt(1, worker.getWorkerId());
			ps.setString(2, worker.getRequestedRecordDate());
			
			logger.debug("[saveHistoryExecutionForManualRetrieval()-QUERY 1] : " + ps.toString());
			
			int rowsInserted = ps.executeUpdate();
			
			if (rowsInserted <= 0){
				throw new DataAccessException("saveHistoryExecutionForManualRetrieval() -> Failed to insert record for history execution in database.");
			}
			generatedKeys = ps.getGeneratedKeys();
			if (generatedKeys.next()){
				idHistoryExecution = generatedKeys.getInt(1);
				logger.info("New history execution inserted, id: " + idHistoryExecution);
				con.commit();
			}
			else{
				throw new DataAccessException("saveHistoryExecutionForManualRetrieval() -> Failed to insert record for history execution in database, no generated key obtained.");
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("saveHistoryExecutionForManualRetrieval", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("saveHistoryExecutionForManualRetrieval() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("saveHistoryExecutionForManualRetrieval", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("saveHistoryExecutionForManualRetrieval() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps, rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("saveHistoryExecutionForManualRetrieval() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return idHistoryExecution;
		
	}
	
	public boolean deletePreviousHistoryForManualRetrieval(Worker worker) throws DataAccessException{
		
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		ResultSet rs = null;
		int idHistoryExecution = 0;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			
			/*Check if previous history records exists for that date*/
			String sql = SQL_SELECT_IF_EXISTS_HISTORY_FOR_MANUALRETRIEVAL.replaceAll("00", Integer.toString(worker.getDomainId()));
			ps = con.prepareStatement(sql);
			ps.setString(1, worker.getRequestedRecordDate());
			ps.setInt(2, worker.getWorkerId());
			rs = ps.executeQuery();
			
			logger.debug("[deletePreviousHistoryForManualRetrieval()-QUERY 1] : " + ps.toString());
			
			if (rs.next()){
				logger.debug("Record already exists for the date, it will be deleted first.");
				idHistoryExecution = rs.getInt("idhistoryexecution");
				
				//Delete location history and then history execution due to the foreign key relationship
				DatabaseConnectionManager.clearResources(ps);
				sql = SQL_DELETE_EXISTING_LOCATION_HISTORY.replaceAll("00", Integer.toString(worker.getDomainId()));
				ps = con.prepareStatement(sql);
				ps.setInt(1, idHistoryExecution);
				logger.debug("[deletePreviousHistoryForManualRetrieval()-QUERY 2] : " + ps.toString());
				
				int rowsUpdated = ps.executeUpdate();
				
				if (rowsUpdated <= 0){
					logger.debug("No record exists in location history for this date");
				}
				else{
					logger.debug("Records found in the location history and deleted");
				}
				
				sql = SQL_DELETE_EXISTING_EXECUTION_HISTORY_FOR_MANUALRETRIEVAL.replaceAll("00", Integer.toString(worker.getDomainId()));
				ps = con.prepareStatement(sql);
				ps.setString(1, worker.getRequestedRecordDate());
				ps.setInt(2, worker.getWorkerId());
				
				logger.debug("[deletePreviousHistoryForManualRetrieval()-QUERY 3] : " + ps.toString());
				
				rowsUpdated = ps.executeUpdate();
				
				if (rowsUpdated <= 0){
					throw new DataAccessException("deletePreviousHistoryForManualRetrieval() -> Failed to delete record for history execution in database.");
				}
				else{
					con.commit();
				}
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("deletePreviousHistoryForManualRetrieval", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("deletePreviousHistoryForManualRetrieval() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("deletePreviousHistoryForManualRetrieval", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("deletePreviousHistoryForManualRetrieval() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps, rs);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("deletePreviousHistoryForManualRetrieval() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		return flag;
		
	}
	
	public boolean updateManualRetrievalRequest(int manualRetrievalId, String comment, int status) throws DataAccessException{
		boolean flag = true;
		
		Connection con = null;
		PreparedStatement ps = null;
		
		try{
			con = DatabaseConnectionManager.getConnection();
			ps = con.prepareStatement(SQL_UPDATE_MANUALRETRIEVAL);
			ps.setInt(1, status);
			ps.setString(2, comment);
			ps.setInt(3, manualRetrievalId);
			
			logger.debug("[updateManualRetrievalRequest()-QUERY] : " + ps.toString());
			
			int rowUpdated = ps.executeUpdate();
			
			if (rowUpdated <= 0){
				throw new DataAccessException("updateManualRetrievalRequest() -> Failed to update for manual retrieval location history in database.");
			}
			else{
				con.commit();
			}
			
		}
		catch(SQLException sqlEx){
			logger.error("updateManualRetrievalRequest", "SQLException occurred in DAO layer : " + sqlEx.getMessage());
			throw new DataAccessException("updateManualRetrievalRequest() -> SQLException occurred in DAO layer : " + sqlEx.getMessage());
		}
		catch(Exception ex){
			logger.error("updateManualRetrievalRequest", "Exception occurred in DAO layer : " + ex.getMessage());
			throw new DataAccessException("updateManualRetrievalRequest() -> Exception occurred in DAO layer : " + ex.getMessage());
		}
		finally{
			try {
				DatabaseConnectionManager.returnConnection(con);
				DatabaseConnectionManager.clearResources(ps);
			} 
			catch (DatabaseConnectionManagerException dcmEx) {
				throw new DataAccessException("updateManualRetrievalRequest() -> DatabaseConnectionManagerException occured during closing resources ", dcmEx);
			}
		}
		
		return flag;
	
	}

}
