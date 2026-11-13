-- Schema for Aiven (uses defaultdb, no CREATE DATABASE needed)

CREATE TABLE IF NOT EXISTS `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `gender` ENUM('male', 'female', 'other') NOT NULL,
  `college_name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `properties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `address` TEXT NOT NULL,
  `city_id` INT NOT NULL,
  `gender` ENUM('male', 'female', 'unisex') NOT NULL DEFAULT 'unisex',
  `rent` DECIMAL(10, 2) NOT NULL,
  `rating_clean` DECIMAL(2, 1) NOT NULL DEFAULT 0.0,
  `rating_food` DECIMAL(2, 1) NOT NULL DEFAULT 0.0,
  `rating_safety` DECIMAL(2, 1) NOT NULL DEFAULT 0.0,
  `description` TEXT DEFAULT NULL,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `interested_users_properties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `property_id` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_property` (`user_id`, `property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `cities` (`id`, `name`) VALUES
(1, 'Delhi'),
(2, 'Mumbai'),
(3, 'Bangalore'),
(4, 'Hyderabad');

INSERT IGNORE INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `gender`, `college_name`) VALUES
(1, 'Rahul Sharma', 'rahul.sharma@example.com', '$2y$10$c9cclk50dm.EoMZdBdElhuEgVwXXyxtWLDjIbXG3Nj49dxxCIpGx2', '9876543210', 'male', 'IIT Delhi'),
(2, 'Priya Patel', 'priya.patel@example.com', '$2y$10$c9cclk50dm.EoMZdBdElhuEgVwXXyxtWLDjIbXG3Nj49dxxCIpGx2', '9876543211', 'female', 'St. Xavier''s College, Mumbai'),
(3, 'Aditya Verma', 'aditya.verma@example.com', '$2y$10$c9cclk50dm.EoMZdBdElhuEgVwXXyxtWLDjIbXG3Nj49dxxCIpGx2', '9876543212', 'male', 'BITS Pilani, Hyderabad'),
(4, 'Sneha Rao', 'sneha.rao@example.com', '$2y$10$c9cclk50dm.EoMZdBdElhuEgVwXXyxtWLDjIbXG3Nj49dxxCIpGx2', '9876543213', 'female', 'RV College of Engineering, Bangalore');

INSERT IGNORE INTO `properties` (`id`, `name`, `address`, `city_id`, `gender`, `rent`, `rating_clean`, `rating_food`, `rating_safety`, `description`) VALUES
(1, 'Saxena''s Paying Guest', 'Plot No. 45, Hudson Lane, Kingsway Camp, Delhi University North Campus, Delhi - 110009', 1, 'male', 9500.00, 4.3, 4.0, 4.6, 'Spacious rooms with attached balconies, high-speed WiFi, 3 times home-cooked food, and 24/7 security. Just 5 minutes walk from GTB Nagar Metro Station.'),
(2, 'Green Park Luxury PG', 'H.No 12A, Block C, Green Park Extension, South Delhi, Delhi - 110016', 1, 'unisex', 14000.00, 4.8, 4.5, 4.7, 'Premium fully-furnished PG with AC, power backup, daily housekeeping, RO water, and biometric entry. Close to AIIMS and Green Park Market.'),
(3, 'Navkar Residency PG', 'B-302, Sea Face Road, Near Bandra Station, Bandra West, Mumbai - 400050', 2, 'female', 16500.00, 4.5, 4.2, 4.9, 'Modern girls PG with sea breeze, round-the-clock CCTV surveillance, biometric security, air conditioning, and laundry services included.'),
(4, 'Youth Ville Hostel', '4th Floor, SV Road, Andheri West, Mumbai - 400058', 2, 'unisex', 12000.00, 4.1, 3.8, 4.3, 'Vibrant co-living space designed for students and working professionals. Includes gaming zone, study lounge, and high-speed fiber internet.'),
(5, 'Zolo Comfort Living', '14th Main Road, Sector 4, HSR Layout, Bengaluru, Karnataka - 560102', 3, 'male', 11500.00, 4.4, 4.1, 4.5, 'Well-maintained PG in prime tech hub. Equipped with private study desks, refrigerator, washing machine, and dedicated bike parking.'),
(6, 'Starlight Ladies PG', '80 Feet Road, 4th Block, Koramangala, Bengaluru, Karnataka - 560034', 3, 'female', 13000.00, 4.7, 4.4, 4.8, 'Safe and peaceful accommodation for female students and corporate professionals. Nutritious North & South Indian meals, lift, and 24/7 warden.'),
(7, 'CyberCity Executive Stays', 'Plot 88, Telecom Nagar, Gachibowli, Hyderabad, Telangana - 500032', 4, 'unisex', 10500.00, 4.2, 4.3, 4.4, 'Located within 10 minutes drive from HITEC City. Fully furnished single and double sharing rooms with power backup, gym access, and cafeteria.'),
(8, 'Royal Palms PG', 'Rd Number 36, Aditya Enclave, Jubilee Hills, Hyderabad, Telangana - 500033', 4, 'male', 8500.00, 4.0, 3.9, 4.2, 'Affordable PG offering clean rooms, high-speed WiFi, attached bathrooms, hot water facility, and 3-course daily meals.');

INSERT IGNORE INTO `interested_users_properties` (`user_id`, `property_id`) VALUES
(1, 1), (1, 3), (2, 2), (2, 3), (2, 6), (3, 1), (3, 7), (4, 5), (4, 6);

