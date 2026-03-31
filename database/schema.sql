-- OVACS Database Schema
-- Online Vehicle Availability Control System
-- Created: March 31, 2026

-- Create database
CREATE DATABASE IF NOT EXISTS ovacs_db;
USE ovacs_db;

-- ===================================
-- CORE TABLES
-- ===================================

-- Stations Table (50 stations)
CREATE TABLE stations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    station_code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    city VARCHAR(50),
    postcode VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    capacity_eru INT DEFAULT 3,
    capacity_ptu INT DEFAULT 2,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Vehicle Types Reference
CREATE TABLE vehicle_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_code VARCHAR(10) NOT NULL UNIQUE,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    equipment_specs TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles Table (150 vehicles)
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id VARCHAR(20) NOT NULL UNIQUE,
    type_id INT NOT NULL,
    station_id INT NOT NULL,
    make VARCHAR(50),
    model VARCHAR(50),
    year INT,
    registration VARCHAR(20),
    mileage INT DEFAULT 0,
    status ENUM('Available', 'In Service', 'Out of Service', 'Maintenance', 'Decommissioned') DEFAULT 'Available',
    last_service_date DATE,
    next_service_date DATE,
    service_interval_km INT DEFAULT 10000,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES vehicle_types(id),
    FOREIGN KEY (station_id) REFERENCES stations(id),
    INDEX idx_status (status),
    INDEX idx_station (station_id),
    INDEX idx_type (type_id)
);

-- ===================================
-- SHIFT MANAGEMENT
-- ===================================

-- Shift Patterns
CREATE TABLE shift_patterns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pattern_name VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration_hours INT NOT NULL,
    pattern_type ENUM('Day', 'Night', 'Split', '24Hour') DEFAULT 'Day',
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- Station Shifts (which shifts run at which stations)
CREATE TABLE station_shifts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    station_id INT NOT NULL,
    pattern_id INT NOT NULL,
    days_of_week VARCHAR(7) DEFAULT '1111111', -- MTWTFSS
    effective_date DATE NOT NULL,
    end_date DATE,
    min_eru_required INT DEFAULT 1,
    min_ptu_required INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (station_id) REFERENCES stations(id),
    FOREIGN KEY (pattern_id) REFERENCES shift_patterns(id)
);

-- ===================================
-- TRACKING & LOGGING
-- ===================================

-- Vehicle Status Log
CREATE TABLE vehicle_status_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    previous_status ENUM('Available', 'In Service', 'Out of Service', 'Maintenance', 'Decommissioned'),
    new_status ENUM('Available', 'In Service', 'Out of Service', 'Maintenance', 'Decommissioned') NOT NULL,
    previous_station_id INT,
    new_station_id INT,
    changed_by VARCHAR(100),
    change_reason TEXT,
    deployment_location TEXT,
    mileage_at_change INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (previous_station_id) REFERENCES stations(id),
    FOREIGN KEY (new_station_id) REFERENCES stations(id),
    INDEX idx_vehicle_timestamp (vehicle_id, timestamp),
    INDEX idx_timestamp (timestamp)
);

-- Maintenance Records
CREATE TABLE maintenance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    maintenance_type ENUM('Scheduled', 'Unscheduled', 'Emergency', 'Inspection') NOT NULL,
    description TEXT NOT NULL,
    service_provider VARCHAR(100),
    cost DECIMAL(10, 2),
    start_date DATE NOT NULL,
    completion_date DATE,
    mileage_at_service INT,
    next_service_km INT,
    parts_replaced TEXT,
    technician_notes TEXT,
    is_complete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    INDEX idx_vehicle_date (vehicle_id, start_date)
);

-- ===================================
-- USER MANAGEMENT
-- ===================================

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('Admin', 'Dispatcher', 'Station Manager', 'Technician', 'Viewer') NOT NULL,
    station_id INT NULL, -- NULL for system-wide access
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES stations(id),
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- ===================================
-- DEPLOYMENT TRACKING
-- ===================================

-- Deployments (active operations)
CREATE TABLE deployments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    call_reference VARCHAR(50),
    deployed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_at TIMESTAMP NULL,
    deployment_type ENUM('Emergency', 'Transport', 'Standby', 'Training') DEFAULT 'Emergency',
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    location_from TEXT,
    location_to TEXT,
    assigned_crew TEXT,
    notes TEXT,
    mileage_out INT,
    mileage_in INT,
    is_complete BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    INDEX idx_vehicle_active (vehicle_id, is_complete),
    INDEX idx_deployed_at (deployed_at)
);

-- ===================================
-- SAMPLE DATA INSERTION
-- ===================================

-- Insert Vehicle Types
INSERT INTO vehicle_types (type_code, type_name, description) VALUES
('ERU', 'Emergency Response Unit', 'Fully equipped ambulance for emergency medical response'),
('PTU', 'Patient Transport Unit', 'Non-emergency patient transport vehicle');

-- Insert Sample Stations (first 10 of 50)
INSERT INTO stations (station_code, name, address, city, postcode, phone, capacity_eru, capacity_ptu) VALUES
('STN001', 'Central Station', '123 Main Street', 'Cityville', 'CV1 2AB', '01234-567001', 4, 2),
('STN002', 'North Station', '456 North Road', 'Cityville', 'CV2 3CD', '01234-567002', 3, 3),
('STN003', 'South Station', '789 South Avenue', 'Cityville', 'CV3 4EF', '01234-567003', 3, 2),
('STN004', 'East Station', '101 East Drive', 'Cityville', 'CV4 5GH', '01234-567004', 2, 2),
('STN005', 'West Station', '202 West Lane', 'Cityville', 'CV5 6IJ', '01234-567005', 3, 2),
('STN006', 'Airport Station', 'Terminal Building', 'Cityville', 'CV6 7KL', '01234-567006', 2, 3),
('STN007', 'Hospital Station', 'General Hospital', 'Cityville', 'CV7 8MN', '01234-567007', 4, 1),
('STN008', 'Industrial Station', 'Industrial Estate', 'Cityville', 'CV8 9OP', '01234-567008', 2, 2),
('STN009', 'Suburban Station', 'Residential Area', 'Cityville', 'CV9 0QR', '01234-567009', 3, 2),
('STN010', 'Highway Station', 'Motorway Services', 'Cityville', 'CV10 1ST', '01234-567010', 2, 1);

-- Insert Sample Vehicles (first 20 of 150)
INSERT INTO vehicles (vehicle_id, type_id, station_id, make, model, year, registration, status) VALUES
('A001', 1, 1, 'Mercedes', 'Sprinter', 2023, 'AB23 XYZ', 'Available'),
('A002', 1, 1, 'Mercedes', 'Sprinter', 2023, 'CD23 UVW', 'In Service'),
('A003', 1, 1, 'Ford', 'Transit', 2022, 'EF22 RST', 'Available'),
('B001', 2, 1, 'Volkswagen', 'Crafter', 2023, 'GH23 OPQ', 'Available'),
('B002', 2, 1, 'Renault', 'Master', 2022, 'IJ22 LMN', 'Available'),
('A004', 1, 2, 'Mercedes', 'Sprinter', 2023, 'KL23 HIJ', 'Available'),
('A005', 1, 2, 'Mercedes', 'Sprinter', 2022, 'MN22 EFG', 'Maintenance'),
('B003', 2, 2, 'Ford', 'Transit', 2023, 'OP23 BCD', 'In Service'),
('B004', 2, 2, 'Volkswagen', 'Crafter', 2022, 'QR22 YZA', 'Available'),
('A006', 1, 3, 'Mercedes', 'Sprinter', 2023, 'ST23 VWX', 'Available'),
('A007', 1, 3, 'Ford', 'Transit', 2023, 'UV23 STU', 'Available'),
('B005', 2, 3, 'Renault', 'Master', 2022, 'WX22 PQR', 'Available'),
('A008', 1, 4, 'Mercedes', 'Sprinter', 2022, 'YZ22 MNO', 'Available'),
('B006', 2, 4, 'Ford', 'Transit', 2023, 'AB24 JKL', 'Out of Service'),
('A009', 1, 5, 'Mercedes', 'Sprinter', 2023, 'CD24 GHI', 'Available'),
('A010', 1, 5, 'Ford', 'Transit', 2022, 'EF23 DEF', 'Available'),
('B007', 2, 5, 'Volkswagen', 'Crafter', 2023, 'GH24 ABC', 'Available'),
('A011', 1, 6, 'Mercedes', 'Sprinter', 2023, 'IJ24 ZYX', 'Available'),
('B008', 2, 6, 'Renault', 'Master', 2022, 'KL23 WVU', 'Available'),
('A012', 1, 7, 'Mercedes', 'Sprinter', 2023, 'MN24 TSR', 'Available');

-- Insert Standard Shift Patterns
INSERT INTO shift_patterns (pattern_name, start_time, end_time, duration_hours, pattern_type, description) VALUES
('Day Shift', '07:00:00', '19:00:00', 12, 'Day', 'Standard 12-hour day shift'),
('Night Shift', '19:00:00', '07:00:00', 12, 'Night', 'Standard 12-hour night shift'),
('Early Shift', '06:00:00', '14:00:00', 8, 'Day', '8-hour early shift'),
('Late Shift', '14:00:00', '22:00:00', 8, 'Day', '8-hour late shift'),
('24 Hour Coverage', '00:00:00', '23:59:59', 24, '24Hour', 'Continuous 24-hour coverage');

-- Create Admin User (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('admin', 'admin@ovacs.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'Admin');

-- Create Database Views for Common Queries
CREATE VIEW vehicle_availability AS
SELECT 
    s.station_code,
    s.name as station_name,
    vt.type_name,
    COUNT(v.id) as total_vehicles,
    SUM(CASE WHEN v.status = 'Available' THEN 1 ELSE 0 END) as available,
    SUM(CASE WHEN v.status = 'In Service' THEN 1 ELSE 0 END) as in_service,
    SUM(CASE WHEN v.status = 'Out of Service' THEN 1 ELSE 0 END) as out_of_service,
    SUM(CASE WHEN v.status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance
FROM stations s
LEFT JOIN vehicles v ON s.id = v.station_id AND v.is_active = TRUE
LEFT JOIN vehicle_types vt ON v.type_id = vt.id
WHERE s.is_active = TRUE
GROUP BY s.id, vt.id
ORDER BY s.station_code, vt.type_code;

-- Create Stored Procedures
DELIMITER //

CREATE PROCEDURE UpdateVehicleStatus(
    IN p_vehicle_id VARCHAR(20),
    IN p_new_status VARCHAR(20),
    IN p_station_id INT,
    IN p_changed_by VARCHAR(100),
    IN p_reason TEXT
)
BEGIN
    DECLARE v_id INT;
    DECLARE v_current_status VARCHAR(20);
    DECLARE v_current_station INT;
    
    -- Get current vehicle details
    SELECT id, status, station_id 
    INTO v_id, v_current_status, v_current_station
    FROM vehicles 
    WHERE vehicle_id = p_vehicle_id AND is_active = TRUE;
    
    IF v_id IS NOT NULL THEN
        -- Update vehicle
        UPDATE vehicles 
        SET status = p_new_status, 
            station_id = COALESCE(p_station_id, station_id),
            updated_at = CURRENT_TIMESTAMP
        WHERE id = v_id;
        
        -- Log the change
        INSERT INTO vehicle_status_log (
            vehicle_id, previous_status, new_status, 
            previous_station_id, new_station_id,
            changed_by, change_reason
        ) VALUES (
            v_id, v_current_status, p_new_status,
            v_current_station, COALESCE(p_station_id, v_current_station),
            p_changed_by, p_reason
        );
    END IF;
END //

DELIMITER ;

-- Indexes for Performance
CREATE INDEX idx_vehicles_status_station ON vehicles(status, station_id);
CREATE INDEX idx_status_log_timestamp ON vehicle_status_log(timestamp DESC);
CREATE INDEX idx_maintenance_due ON vehicles(next_service_date);

-- Grant Permissions (adjust as needed)
-- CREATE USER 'ovacs_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE ON ovacs_db.* TO 'ovacs_user'@'localhost';
-- FLUSH PRIVILEGES;