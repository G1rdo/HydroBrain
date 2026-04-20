-- create
CREATE TABLE ph (
  id INT NOT NULL auto_increment PRIMARY KEY,
  probe_name ENUM('PH-BTA','E-312'),  
  ph decimal (8, 6),
  sql_timestamp TIMESTAMP,
  sensor_timestamp TIMESTAMP
);

-- insert
INSERT INTO ph 
    (probe_name, ph, sensor_timestamp) VALUES 
    ('PH-BTA', 3.14149, '2023-4-21 12:1'),
    ('PH-BTA', 30.222, '2024-07-22 12:12:12'),
    ('E-312', 11.0011111111, '2001-07-22 12:12:12');


-- fetch 
SELECT * FROM ph
