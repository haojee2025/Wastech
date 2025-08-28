INSERT INTO item_types (name, hazardous) VALUES
('Smartphone', 0),
('Laptop', 0),
('Battery', 1),
('Tablet', 0),
('Other', 0);

-- Example machines (edit locations/capacity as you like)
INSERT INTO machines (code, name, location, max_volume_l, max_weight_kg)
VALUES
('WS-A01','Wastech Bin A01','Block A - Lobby', 120.000, 80.000),
('WS-B02','Wastech Bin B02','Block B - Cafeteria', 150.000, 100.000),
('WS-C03','Wastech Bin C03','Block C - Parking', 200.000, 150.000);
