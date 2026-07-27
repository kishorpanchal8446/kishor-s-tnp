-- =============================================================================
-- ASTPMS 2.0 — Master Database Schema & Seed Data
-- Database: astpms
-- Server: MySQL 8.0+ / MariaDB (Port 3307)
-- Fully Normalized, Foreign Keys with ON DELETE CASCADE, Indexes, Timestamps
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `astpms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `astpms`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Admins Table
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin', 'admin', 'coordinator') NOT NULL DEFAULT 'admin',
  `phone` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Students Table
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(30) NOT NULL UNIQUE,
  `enrollment_number` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `parent_phone` VARCHAR(20) DEFAULT NULL,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `dob` DATE DEFAULT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `semester` INT DEFAULT 7,
  `cgpa` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `passing_year` INT NOT NULL,
  `address` TEXT DEFAULT NULL,
  `profile_pic` VARCHAR(255) DEFAULT 'assets/images/default_avatar.png',
  `placement_status` ENUM('Unplaced', 'Placed', 'Opted Out') DEFAULT 'Unplaced',
  `placed_company` VARCHAR(100) DEFAULT NULL,
  `placed_package` DECIMAL(8,2) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_verified` TINYINT(1) DEFAULT 1,
  `linkedin_url` VARCHAR(255) DEFAULT NULL,
  `github_url` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`branch`),
  INDEX (`cgpa`),
  INDEX (`placement_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Companies Table
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `industry` VARCHAR(100) DEFAULT 'Technology',
  `website` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT 'Pune',
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Jobs Table
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `role` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `eligibility_criteria` TEXT DEFAULT NULL,
  `min_cgpa` DECIMAL(4,2) NOT NULL DEFAULT 6.00,
  `eligible_branches` VARCHAR(255) NOT NULL DEFAULT 'Computer Science,Information Technology',
  `location` VARCHAR(100) NOT NULL DEFAULT 'Pune',
  `package_lpa` DECIMAL(8,2) NOT NULL DEFAULT 5.00,
  `job_type` ENUM('Full Time', 'Internship', 'Internship + Full Time') DEFAULT 'Full Time',
  `deadline` DATE NOT NULL,
  `status` ENUM('Open', 'Closed', 'Draft') DEFAULT 'Open',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Applications Table
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `status` ENUM('Applied', 'Shortlisted', 'Interview Scheduled', 'Selected', 'Rejected') DEFAULT 'Applied',
  `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_app` (`job_id`, `student_id`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Interviews Table
DROP TABLE IF EXISTS `interviews`;
CREATE TABLE `interviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL,
  `round_type` VARCHAR(100) NOT NULL DEFAULT 'Technical Round',
  `scheduled_date` DATE NOT NULL,
  `scheduled_time` TIME NOT NULL,
  `venue` VARCHAR(255) DEFAULT 'Online Meeting',
  `join_url` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Training Programs Table
DROP TABLE IF EXISTS `training_programs`;
CREATE TABLE `training_programs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `trainer` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `venue` VARCHAR(150) DEFAULT 'Seminar Hall B',
  `status` ENUM('Upcoming', 'Ongoing', 'Completed') DEFAULT 'Upcoming',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Training Registrations Table
DROP TABLE IF EXISTS `training_registrations`;
CREATE TABLE `training_registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `training_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `status` ENUM('Registered', 'Attended', 'Completed') DEFAULT 'Registered',
  `registered_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_train_reg` (`training_id`, `student_id`),
  FOREIGN KEY (`training_id`) REFERENCES `training_programs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Higher Studies Table
DROP TABLE IF EXISTS `higher_studies`;
CREATE TABLE `higher_studies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `target_degree` VARCHAR(100) NOT NULL DEFAULT 'M.Tech / MS',
  `exam_name` VARCHAR(50) DEFAULT 'GATE / GRE',
  `exam_score` VARCHAR(50) DEFAULT NULL,
  `target_university` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('Preparing', 'Applied', 'Admitted') DEFAULT 'Preparing',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Notifications Table
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `recipient_id` INT NOT NULL,
  `recipient_role` ENUM('student', 'admin', 'company') NOT NULL DEFAULT 'student',
  `sender_role` ENUM('admin', 'company', 'system') NOT NULL DEFAULT 'admin',
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('job', 'interview', 'training', 'system') DEFAULT 'system',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Activity Logs Table
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `user_role` ENUM('student', 'admin', 'company') NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Settings Table
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Contact Messages Table
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Student Skills Table
DROP TABLE IF EXISTS `student_skills`;
CREATE TABLE `student_skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Student Documents / Resumes Table
DROP TABLE IF EXISTS `student_documents`;
CREATE TABLE `student_documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `doc_type` VARCHAR(50) DEFAULT 'resume',
  `file_path` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_size` INT DEFAULT 0,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Announcements Table
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `priority` ENUM('Normal', 'High', 'Urgent') DEFAULT 'Normal',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Email Verification Tokens
DROP TABLE IF EXISTS `email_verification_tokens`;
CREATE TABLE `email_verification_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `otp` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Password Reset Tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- SEED DATA INSERTION
-- Passwords:
-- Admin:   admin123
-- Student: student123
-- Company: company123
-- =============================================================================

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Super Administrator', 'admin@astpms.edu', '$2y$10$gy25iAk.ipupN3vaQau3GeRJWWSqWAorvq1DvQdRDcbxlUnfIjzRu', 'superadmin'),
(2, 'Training Coordinator', 'training@astpms.edu', '$2y$10$gy25iAk.ipupN3vaQau3GeRJWWSqWAorvq1DvQdRDcbxlUnfIjzRu', 'coordinator'),
(3, 'Placement Officer', 'placement@astpms.edu', '$2y$10$gy25iAk.ipupN3vaQau3GeRJWWSqWAorvq1DvQdRDcbxlUnfIjzRu', 'admin');

INSERT INTO `students` (`id`, `student_id`, `enrollment_number`, `name`, `email`, `password`, `phone`, `gender`, `branch`, `department`, `semester`, `cgpa`, `passing_year`, `placement_status`, `placed_company`, `placed_package`) VALUES
(1, 'STU2026001', 'ENR20260001', 'Arjun Sharma', 'arjun.sharma@astpms.edu', '$2y$10$l3Xnup4ywjfqtuhTPoXWR.LdUcuGtYEY1e7kgEO3eN/6IDVFYlr6S', '+91-9876543210', 'Male', 'Computer Science', 'Computer Engineering', 7, 8.75, 2026, 'Unplaced', NULL, NULL),
(2, 'STU2026002', 'ENR20260002', 'Priya Patel', 'priya.patel@astpms.edu', '$2y$10$l3Xnup4ywjfqtuhTPoXWR.LdUcuGtYEY1e7kgEO3eN/6IDVFYlr6S', '+91-9876543211', 'Female', 'Information Technology', 'IT Engineering', 7, 9.10, 2026, 'Placed', 'Google India', 25.00),
(3, 'STU2026003', 'ENR20260003', 'Rahul Mehta', 'rahul.mehta@astpms.edu', '$2y$10$l3Xnup4ywjfqtuhTPoXWR.LdUcuGtYEY1e7kgEO3eN/6IDVFYlr6S', '+91-9876543212', 'Male', 'Electronics & Communication', 'ENTC Engineering', 7, 7.80, 2026, 'Unplaced', NULL, NULL);

INSERT INTO `companies` (`id`, `name`, `email`, `password`, `phone`, `industry`, `website`, `city`) VALUES
(1, 'Wipro Limited', 'hr@wipro.com', '$2y$10$KtlffQ8LJQEgN06roHNdL.LfG8wkYvdCL5tm3v13vsrGzn2N6OEHO', '+91-2012345678', 'Information Technology', 'https://wipro.com', 'Pune'),
(2, 'Infosys Limited', 'hr@infosys.com', '$2y$10$KtlffQ8LJQEgN06roHNdL.LfG8wkYvdCL5tm3v13vsrGzn2N6OEHO', '+91-2087654321', 'Digital Services & Consulting', 'https://infosys.com', 'Bengaluru'),
(3, 'Tata Consultancy Services', 'hr@tcs.com', '$2y$10$KtlffQ8LJQEgN06roHNdL.LfG8wkYvdCL5tm3v13vsrGzn2N6OEHO', '+91-2299887766', 'IT Services', 'https://tcs.com', 'Mumbai');

INSERT INTO `jobs` (`id`, `company_id`, `role`, `description`, `min_cgpa`, `eligible_branches`, `location`, `package_lpa`, `job_type`, `deadline`, `status`) VALUES
(1, 1, 'Project Engineer', 'Development of enterprise Java & Full Stack web applications.', 6.50, 'Computer Science,Information Technology', 'Pune', 6.50, 'Full Time', '2026-08-30', 'Open'),
(2, 2, 'Systems Engineer', 'Cloud infrastructure maintenance and microservice development.', 7.00, 'Computer Science,Information Technology,Electronics & Communication', 'Bengaluru', 7.20, 'Full Time', '2026-09-15', 'Open'),
(3, 3, 'Assistant Systems Engineer', 'Software engineering, automated testing, and DevOps deployment.', 6.00, 'Computer Science,Information Technology,Electronics & Communication,Electrical', 'Mumbai', 5.50, 'Full Time', '2026-09-30', 'Open');

INSERT INTO `announcements` (`id`, `title`, `content`, `priority`) VALUES
(1, 'Infosys Campus Drive Announced', 'Infosys Systems Engineer drive is scheduled for September 2026. Register on ASTPMS portal.', 'High'),
(2, 'Resume Review Session', 'T&P cell is conducting mandatory resume verification for all 7th semester students.', 'Normal');

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'ASTPMS — Training & Placement Management System'),
('college_name', 'University College of Engineering'),
('placement_officer_name', 'Dr. R. K. Verma'),
('placement_email', 'placement@astpms.edu');
