
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wedding_id` int DEFAULT NULL,
  `event_name` varchar(100)  DEFAULT NULL,
  `event_date_time` datetime DEFAULT NULL,
  `location_name` varchar(255)  DEFAULT NULL,
  `google_map_link` text ,
  PRIMARY KEY (`id`),
  KEY `wedding_id` (`wedding_id`)
);




DROP TABLE IF EXISTS `gallery`;
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wedding_id` int DEFAULT NULL,
  `image_path` varchar(255)  DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wedding_id` (`wedding_id`)
);



DROP TABLE IF EXISTS `guests`;
CREATE TABLE IF NOT EXISTS `guests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wedding_id` int DEFAULT NULL,
  `name` varchar(150)  DEFAULT NULL,
  `whatsapp_number` varchar(20)  DEFAULT NULL,
  `category` varchar(50)  DEFAULT NULL,
  `side` varchar(50)  DEFAULT NULL,
  `is_opened` tinyint(1) DEFAULT '0',
  `opened_at` datetime DEFAULT NULL,
  `rsvp_status` enum('pending','accepted','rejected')  DEFAULT 'pending',
  `guest_note` text  DEFAULT NULL,
  `seats_reserved` int DEFAULT '1',
  `is_sent` TINYINT(1) DEFAULT '0',
  `sent_at` DATETIME NULL,
  `invite_token` VARCHAR(20) DEFAULT NULL,
 
  PRIMARY KEY (`id`),
  KEY `wedding_id` (`wedding_id`)
);


CREATE UNIQUE INDEX idx_guests_invite_token ON guests (invite_token);
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wedding_id` int DEFAULT NULL,
  `task_name` varchar(255)  DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `wedding_id` (`wedding_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100)  DEFAULT NULL,
  `email` varchar(100)  DEFAULT NULL,
  `password` varchar(255)  DEFAULT NULL,
  `role` enum('admin','couple')  DEFAULT 'couple',
  `status` enum('pending','active')  DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_slip` varchar(255)  DEFAULT NULL,
  `deletion_notice_sent_at` DATETIME DEFAULT NULL,
  `refund_requested_at` DATETIME NULL,
  `refund_status` ENUM('none', 'pending', 'approved', 'details_submitted', 'rejected', 'completed') DEFAULT 'none',
  `refund_bank_details` TEXT NULL,
  `refund_reason` TEXT NULL,
  `package` ENUM('basic', 'standard', 'premium') DEFAULT 'basic';
  `has_guest_gallery` TINYINT(1) DEFAULT 0;
  `upgrade_slip` VARCHAR(255) NULL;
  `pending_upgrade_plan` VARCHAR(100) NULL;
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);



-- Table structure for table `weddings`
--

DROP TABLE IF EXISTS `weddings`;
CREATE TABLE IF NOT EXISTS `weddings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `bride_name` varchar(100)  DEFAULT NULL,
  `groom_name` varchar(100)  DEFAULT NULL,
  `wedding_date` date DEFAULT NULL,
  `cover_image` varchar(255)  DEFAULT NULL,
  `love_story` text  DEFAULT NULL,
  `hero_image` varchar(255)   DEFAULT NULL,
  `template_name` varchar(100)   DEFAULT 'default',
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
);
ALTER TABLE weddings ADD COLUMN venue VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS guest_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT,
    guest_name VARCHAR(150),
    image_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE
);