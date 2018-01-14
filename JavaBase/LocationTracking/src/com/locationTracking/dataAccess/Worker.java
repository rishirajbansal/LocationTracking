package com.locationTracking.dataAccess;

import java.sql.Timestamp;

public class Worker {
	
	private int workerId;
	
	private int domainId;
	
	private String name;
	
	private String email;
	
	private String password;
	
	private String decodedPassword;
	
	private String status;
	
	private Timestamp createdOn;
	
	private int manualRetrievalId;
	
	private String requestedRecordDate;
	
	

	public int getWorkerId() {
		return workerId;
	}

	public void setWorkerId(int workerId) {
		this.workerId = workerId;
	}

	public int getDomainId() {
		return domainId;
	}

	public void setDomainId(int domainId) {
		this.domainId = domainId;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public String getEmail() {
		return email;
	}

	public void setEmail(String email) {
		this.email = email;
	}

	public String getPassword() {
		return password;
	}

	public void setPassword(String password) {
		this.password = password;
	}

	public String getStatus() {
		return status;
	}

	public void setStatus(String status) {
		this.status = status;
	}

	public Timestamp getCreatedOn() {
		return createdOn;
	}

	public void setCreatedOn(Timestamp createdOn) {
		this.createdOn = createdOn;
	}

	public String getDecodedPassword() {
		return decodedPassword;
	}

	public void setDecodedPassword(String decodedPassword) {
		this.decodedPassword = decodedPassword;
	}

	public final int getManualRetrievalId() {
		return manualRetrievalId;
	}

	public final void setManualRetrievalId(int manualRetrievalId) {
		this.manualRetrievalId = manualRetrievalId;
	}

	public final String getRequestedRecordDate() {
		return requestedRecordDate;
	}

	public final void setRequestedRecordDate(String requestedRecordDate) {
		this.requestedRecordDate = requestedRecordDate;
	}
	

}
