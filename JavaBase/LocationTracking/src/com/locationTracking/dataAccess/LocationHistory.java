package com.locationTracking.dataAccess;

import java.sql.Timestamp;

public class LocationHistory {
	
	private int idLocationHistory;
	
	private int idworkers;
	
	private int idHistoryExecution;
	
	private Timestamp timestamp;
	
	private String timestampString;
	
	private String formattedTimestamp;
	
	private String applicationBasedTimestamp;
	
	private String latitude;
	
	private String longitude;
	
	private String location;
	

	public int getIdLocationHistory() {
		return idLocationHistory;
	}

	public void setIdLocationHistory(int idLocationHistory) {
		this.idLocationHistory = idLocationHistory;
	}

	public int getIdworkers() {
		return idworkers;
	}

	public void setIdworkers(int idworkers) {
		this.idworkers = idworkers;
	}

	public Timestamp getTimestamp() {
		return timestamp;
	}

	public void setTimestamp(Timestamp timestamp) {
		this.timestamp = timestamp;
	}

	public String getLatitude() {
		return latitude;
	}

	public void setLatitude(String latitude) {
		this.latitude = latitude;
	}

	public String getLongitude() {
		return longitude;
	}

	public void setLongitude(String longitude) {
		this.longitude = longitude;
	}

	public int getIdHistoryExecution() {
		return idHistoryExecution;
	}

	public void setIdHistoryExecution(int idHistoryExecution) {
		this.idHistoryExecution = idHistoryExecution;
	}

	public String getTimestampString() {
		return timestampString;
	}

	public void setTimestampString(String timestampString) {
		this.timestampString = timestampString;
	}

	public String getFormattedTimestamp() {
		return formattedTimestamp;
	}

	public void setFormattedTimestamp(String formattedTimestamp) {
		this.formattedTimestamp = formattedTimestamp;
	}

	public String getLocation() {
		return location;
	}

	public void setLocation(String location) {
		this.location = location;
	}

	public String getApplicationBasedTimestamp() {
		return applicationBasedTimestamp;
	}

	public void setApplicationBasedTimestamp(String applicationBasedTimestamp) {
		this.applicationBasedTimestamp = applicationBasedTimestamp;
	}
	
	
}
