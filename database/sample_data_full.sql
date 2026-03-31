-- OVACS Full Sample Data
-- Populate with 150 vehicles across 50 stations

USE ovacs_db;

-- Clear existing sample data (keep structure)
TRUNCATE TABLE deployments;
TRUNCATE TABLE maintenance_records;
TRUNCATE TABLE vehicle_status_log;
DELETE FROM vehicles WHERE id > 0;
DELETE FROM stations WHERE id > 10; -- Keep first 10, add 40 more

-- Insert all 50 stations
INSERT INTO stations (station_code, name, address, city, postcode, phone, capacity_eru, capacity_ptu) VALUES
-- Stations 11-50 (first 10 already exist)
('STN011', 'Regional Station', '303 Regional Road', 'Cityville', 'CV11 2UV', '01234-567011', 3, 2),
('STN012', 'Downtown Station', '404 Downtown Street', 'Cityville', 'CV12 3WX', '01234-567012', 4, 1),
('STN013', 'Uptown Station', '505 Uptown Avenue', 'Cityville', 'CV13 4YZ', '01234-567013', 2, 3),
('STN014', 'Riverside Station', '606 Riverside Drive', 'Cityville', 'CV14 5AB', '01234-567014', 3, 2),
('STN015', 'Hillside Station', '707 Hillside Road', 'Cityville', 'CV15 6CD', '01234-567015', 2, 2),
('STN016', 'Parkview Station', '808 Park Avenue', 'Cityville', 'CV16 7EF', '01234-567016', 3, 2),
('STN017', 'Lakewood Station', '909 Lake Street', 'Cityville', 'CV17 8GH', '01234-567017', 2, 3),
('STN018', 'Greenfield Station', '1010 Green Lane', 'Cityville', 'CV18 9IJ', '01234-567018', 3, 1),
('STN019', 'Oakwood Station', '1111 Oak Drive', 'Cityville', 'CV19 0KL', '01234-567019', 2, 2),
('STN020', 'Pinewood Station', '1212 Pine Road', 'Cityville', 'CV20 1MN', '01234-567020', 3, 2),
('STN021', 'Maple Station', '1313 Maple Street', 'Cityville', 'CV21 2OP', '01234-567021', 2, 2),
('STN022', 'Cedar Station', '1414 Cedar Avenue', 'Cityville', 'CV22 3QR', '01234-567022', 3, 2),
('STN023', 'Willow Station', '1515 Willow Way', 'Cityville', 'CV23 4ST', '01234-567023', 2, 3),
('STN024', 'Birch Station', '1616 Birch Boulevard', 'Cityville', 'CV24 5UV', '01234-567024', 3, 1),
('STN025', 'Elm Station', '1717 Elm Street', 'Cityville', 'CV25 6WX', '01234-567025', 2, 2),
('STN026', 'Springfield Station', '1818 Spring Road', 'Cityville', 'CV26 7YZ', '01234-567026', 3, 2),
('STN027', 'Fairview Station', '1919 Fair Avenue', 'Cityville', 'CV27 8AB', '01234-567027', 2, 2),
('STN028', 'Highland Station', '2020 Highland Drive', 'Cityville', 'CV28 9CD', '01234-567028', 3, 2),
('STN029', 'Valley Station', '2121 Valley Lane', 'Cityville', 'CV29 0EF', '01234-567029', 2, 3),
('STN030', 'Ridge Station', '2222 Ridge Road', 'Cityville', 'CV30 1GH', '01234-567030', 3, 1),
('STN031', 'Meadow Station', '2323 Meadow Street', 'Cityville', 'CV31 2IJ', '01234-567031', 2, 2),
('STN032', 'Brook Station', '2424 Brook Avenue', 'Cityville', 'CV32 3KL', '01234-567032', 3, 2),
('STN033', 'Creek Station', '2525 Creek Drive', 'Cityville', 'CV33 4MN', '01234-567033', 2, 2),
('STN034', 'River Station', '2626 River Road', 'Cityville', 'CV34 5OP', '01234-567034', 3, 2),
('STN035', 'Forest Station', '2727 Forest Lane', 'Cityville', 'CV35 6QR', '01234-567035', 2, 3),
('STN036', 'Grove Station', '2828 Grove Street', 'Cityville', 'CV36 7ST', '01234-567036', 3, 1),
('STN037', 'Park Station', '2929 Park Avenue', 'Cityville', 'CV37 8UV', '01234-567037', 2, 2),
('STN038', 'Garden Station', '3030 Garden Drive', 'Cityville', 'CV38 9WX', '01234-567038', 3, 2),
('STN039', 'Field Station', '3131 Field Road', 'Cityville', 'CV39 0YZ', '01234-567039', 2, 2),
('STN040', 'Farm Station', '3232 Farm Lane', 'Cityville', 'CV40 1AB', '01234-567040', 3, 2),
('STN041', 'Mill Station', '3333 Mill Street', 'Cityville', 'CV41 2CD', '01234-567041', 2, 3),
('STN042', 'Bridge Station', '3434 Bridge Avenue', 'Cityville', 'CV42 3EF', '01234-567042', 3, 1),
('STN043', 'Tower Station', '3535 Tower Drive', 'Cityville', 'CV43 4GH', '01234-567043', 2, 2),
('STN044', 'Square Station', '3636 Central Square', 'Cityville', 'CV44 5IJ', '01234-567044', 3, 2),
('STN045', 'Circle Station', '3737 Circle Road', 'Cityville', 'CV45 6KL', '01234-567045', 2, 2),
('STN046', 'Corner Station', '3838 Corner Lane', 'Cityville', 'CV46 7MN', '01234-567046', 3, 2),
('STN047', 'Junction Station', '3939 Junction Street', 'Cityville', 'CV47 8OP', '01234-567047', 2, 3),
('STN048', 'Cross Station', '4040 Cross Avenue', 'Cityville', 'CV48 9QR', '01234-567048', 3, 1),
('STN049', 'Point Station', '4141 Point Drive', 'Cityville', 'CV49 0ST', '01234-567049', 2, 2),
('STN050', 'End Station', '4242 End Road', 'Cityville', 'CV50 1UV', '01234-567050', 3, 2);

-- Generate 150 vehicles (75 ERU, 75 PTU) distributed across all 50 stations
-- ERU vehicles (A001-A075)
INSERT INTO vehicles (vehicle_id, type_id, station_id, make, model, year, registration, status) VALUES
-- Station 1-10 ERUs
('A001', 1, 1, 'Mercedes', 'Sprinter', 2023, 'AB23 XYZ', 'Available'),
('A002', 1, 1, 'Mercedes', 'Sprinter', 2023, 'CD23 UVW', 'In Service'),
('A003', 1, 1, 'Ford', 'Transit', 2022, 'EF22 RST', 'Available'),
('A004', 1, 2, 'Mercedes', 'Sprinter', 2023, 'KL23 HIJ', 'Available'),
('A005', 1, 2, 'Mercedes', 'Sprinter', 2022, 'MN22 EFG', 'Maintenance'),
('A006', 1, 3, 'Mercedes', 'Sprinter', 2023, 'ST23 VWX', 'Available'),
('A007', 1, 3, 'Ford', 'Transit', 2023, 'UV23 STU', 'Available'),
('A008', 1, 4, 'Mercedes', 'Sprinter', 2022, 'YZ22 MNO', 'Available'),
('A009', 1, 5, 'Mercedes', 'Sprinter', 2023, 'CD24 GHI', 'Available'),
('A010', 1, 5, 'Ford', 'Transit', 2022, 'EF23 DEF', 'Available'),
('A011', 1, 6, 'Mercedes', 'Sprinter', 2023, 'IJ24 ZYX', 'Available'),
('A012', 1, 7, 'Mercedes', 'Sprinter', 2023, 'MN24 TSR', 'Available'),
('A013', 1, 8, 'Ford', 'Transit', 2023, 'PQ24 MLK', 'Available'),
('A014', 1, 9, 'Mercedes', 'Sprinter', 2022, 'RS24 JHG', 'In Service'),
('A015', 1, 10, 'Ford', 'Transit', 2023, 'TU24 FED', 'Available');

-- Continue with more ERU vehicles for stations 11-50
-- (I'll show a pattern - in reality you'd want to distribute them properly)
INSERT INTO vehicles (vehicle_id, type_id, station_id, make, model, year, registration, status) 
SELECT 
    CONCAT('A', LPAD(15 + n, 3, '0')) as vehicle_id,
    1 as type_id,
    ((n-1) % 50) + 1 as station_id,
    CASE (n % 3) 
        WHEN 0 THEN 'Mercedes'
        WHEN 1 THEN 'Ford'
        ELSE 'Volkswagen'
    END as make,
    CASE (n % 3)
        WHEN 0 THEN 'Sprinter'
        WHEN 1 THEN 'Transit'
        ELSE 'Crafter'
    END as model,
    CASE WHEN n % 4 = 0 THEN 2022 ELSE 2023 END as year,
    CONCAT(
        CHAR(65 + (n % 26)), 
        CHAR(65 + ((n+1) % 26)),
        LPAD((n % 100), 2, '0'),
        ' ',
        CHAR(65 + ((n+2) % 26)),
        CHAR(65 + ((n+3) % 26)),
        CHAR(65 + ((n+4) % 26))
    ) as registration,
    CASE (n % 10)
        WHEN 0 THEN 'Maintenance'
        WHEN 1 THEN 'In Service'
        WHEN 2 THEN 'Out of Service'
        ELSE 'Available'
    END as status
FROM (
    SELECT @row := @row + 1 as n
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) t1,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) t2,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3) t3,
         (SELECT @row:=0) r
    LIMIT 60
) numbers;

-- PTU vehicles (B001-B075)
INSERT INTO vehicles (vehicle_id, type_id, station_id, make, model, year, registration, status) VALUES
-- Station 1-10 PTUs
('B001', 2, 1, 'Volkswagen', 'Crafter', 2023, 'GH23 OPQ', 'Available'),
('B002', 2, 1, 'Renault', 'Master', 2022, 'IJ22 LMN', 'Available'),
('B003', 2, 2, 'Ford', 'Transit', 2023, 'OP23 BCD', 'In Service'),
('B004', 2, 2, 'Volkswagen', 'Crafter', 2022, 'QR22 YZA', 'Available'),
('B005', 2, 3, 'Renault', 'Master', 2022, 'WX22 PQR', 'Available'),
('B006', 2, 4, 'Ford', 'Transit', 2023, 'AB24 JKL', 'Out of Service'),
('B007', 2, 5, 'Volkswagen', 'Crafter', 2023, 'GH24 ABC', 'Available'),
('B008', 2, 6, 'Renault', 'Master', 2022, 'KL23 WVU', 'Available'),
('B009', 2, 7, 'Ford', 'Transit', 2023, 'MN24 TSR', 'Available'),
('B010', 2, 8, 'Volkswagen', 'Crafter', 2022, 'PQ24 ONM', 'Available'),
('B011', 2, 9, 'Renault', 'Master', 2023, 'RS24 LKJ', 'Maintenance'),
('B012', 2, 10, 'Ford', 'Transit', 2022, 'TU24 IHG', 'Available');

-- Continue with more PTU vehicles
INSERT INTO vehicles (vehicle_id, type_id, station_id, make, model, year, registration, status) 
SELECT 
    CONCAT('B', LPAD(12 + n, 3, '0')) as vehicle_id,
    2 as type_id,
    ((n-1) % 50) + 1 as station_id,
    CASE (n % 3) 
        WHEN 0 THEN 'Volkswagen'
        WHEN 1 THEN 'Renault'
        ELSE 'Ford'
    END as make,
    CASE (n % 3)
        WHEN 0 THEN 'Crafter'
        WHEN 1 THEN 'Master'
        ELSE 'Transit'
    END as model,
    CASE WHEN n % 3 = 0 THEN 2022 ELSE 2023 END as year,
    CONCAT(
        CHAR(65 + ((n+5) % 26)), 
        CHAR(65 + ((n+6) % 26)),
        LPAD((n % 100) + 50, 2, '0'),
        ' ',
        CHAR(65 + ((n+7) % 26)),
        CHAR(65 + ((n+8) % 26)),
        CHAR(65 + ((n+9) % 26))
    ) as registration,
    CASE (n % 12)
        WHEN 0 THEN 'Maintenance'
        WHEN 1 THEN 'In Service'
        WHEN 2 THEN 'Out of Service'  
        WHEN 3 THEN 'In Service'
        ELSE 'Available'
    END as status
FROM (
    SELECT @row2 := @row2 + 1 as n
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) t1,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) t2,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3) t3,
         (SELECT @row2:=0) r
    LIMIT 63
) numbers;

-- Add some realistic status log entries
INSERT INTO vehicle_status_log (vehicle_id, previous_status, new_status, new_station_id, changed_by, change_reason, timestamp) 
SELECT 
    v.id,
    'Available' as previous_status,
    'In Service' as new_status,
    v.station_id,
    'System' as changed_by,
    'Emergency deployment' as change_reason,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 24) HOUR) as timestamp
FROM vehicles v 
WHERE v.status = 'In Service' 
LIMIT 10;

-- Add some maintenance records
INSERT INTO maintenance_records (vehicle_id, maintenance_type, description, start_date, completion_date, is_complete)
SELECT 
    id,
    'Scheduled' as maintenance_type,
    'Routine service and safety check' as description,
    DATE_SUB(CURDATE(), INTERVAL 30 DAY) as start_date,  
    DATE_SUB(CURDATE(), INTERVAL 29 DAY) as completion_date,
    TRUE as is_complete
FROM vehicles
WHERE status = 'Maintenance'
LIMIT 5;

-- Update next service dates
UPDATE vehicles 
SET next_service_date = DATE_ADD(CURDATE(), INTERVAL 90 DAY)
WHERE next_service_date IS NULL;

-- Final summary query
SELECT 
    'Fleet Summary' as summary,
    COUNT(*) as total_vehicles,
    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
    SUM(CASE WHEN status = 'In Service' THEN 1 ELSE 0 END) as in_service,
    SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance,
    SUM(CASE WHEN status = 'Out of Service' THEN 1 ELSE 0 END) as out_of_service
FROM vehicles;