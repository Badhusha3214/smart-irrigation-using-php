CREATE DATABASE smart_irrigation;
USE smart_irrigation;

-- Users Table
CREATE TABLE users (
    id VARCHAR(10) PRIMARY KEY NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL, -- For hashed passwords
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(username),
    UNIQUE(email)
);

-- Irrigation Schedule Table
CREATE TABLE irrigation_schedule (
    id VARCHAR(10) PRIMARY KEY NOT NULL,
    schedule_name VARCHAR(20) NOT NULL,
    start_time VARCHAR(20) NOT NULL,
    end_time VARCHAR(20) NOT NULL,
    days_of_week VARCHAR(20) NOT NULL,
    active VARCHAR(10) NOT NULL
);

-- Sensor Data Table
CREATE TABLE sensor_data (
    id VARCHAR(20) PRIMARY KEY NOT NULL,
    sensor_id VARCHAR(20) NOT NULL,
    moisture_level VARCHAR(20) NOT NULL,
    recorded_at VARCHAR(20) NOT NULL
);

-- Water Control Table
CREATE TABLE water_control (
    id VARCHAR(10) PRIMARY KEY NOT NULL,
    action VARCHAR(20) NOT NULL,
    performed_at VARCHAR(20) NOT NULL
);