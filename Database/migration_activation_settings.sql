-- Activation Settings Table
-- This table stores the activation status for various features in the system

CREATE TABLE IF NOT EXISTS `activation_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activation_name` varchar(100) NOT NULL,
  `activation_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Disabled, 1=Enabled',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default activation settings
INSERT INTO `activation_settings` (`activation_name`, `activation_status`) VALUES
('Student Enrollment', 0),
('Form 137 and 138 Page', 0),
('Student Progress Page', 0),
('Teacher Registration', 0);
