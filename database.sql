-- ============================================================================
-- COVID-19 TEST & VACCINATION SYSTEM (ORS) - PHP & MySQL DATABASE
-- Compatible with XAMPP, WAMP, LAMP, and Live Apache/MySQL Hosts
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `covid_ors_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `covid_ors_db`;

-- 1. USERS TABLE
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'hospital', 'patient') NOT NULL DEFAULT 'patient',
  `phone` VARCHAR(30) NOT NULL,
  `cnic` VARCHAR(30) DEFAULT NULL,
  `age` INT DEFAULT NULL,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `city` VARCHAR(100) DEFAULT 'Lahore',
  `address` TEXT DEFAULT NULL,
  `blood_group` VARCHAR(10) DEFAULT 'B+',
  `allergies` TEXT DEFAULT NULL,
  `emergency_contact` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `cnic`, `age`, `gender`, `city`, `address`, `blood_group`, `allergies`, `emergency_contact`, `status`, `avatar`) VALUES
('usr_admin', 'System Administrator', 'admin@covid.gov', 'admin', 'admin', '0800-222-111', '00000-0000000-0', 38, 'Male', 'Islamabad', 'National Health Command Center', 'O+', 'None', '0800-1122', 'Active', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop'),
('pat_ali', 'Ali Khan', 'ali@gmail.com', 'ali', 'patient', '03001234567', '42101-1234567-1', 28, 'Male', 'Lahore', 'House 12, Gulberg III', 'B+', 'None', '03219988776 (Brother)', 'Active', 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=120&h=120&fit=crop'),
('pat_sana', 'Sana Ahmed', 'sana@gmail.com', 'sana', 'patient', '03007654321', '42101-7654321-2', 25, 'Female', 'Karachi', 'Flat 402, Clifton Block 5', 'O+', 'Penicillin', '03331122334 (Father)', 'Active', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=120&h=120&fit=crop'),
('pat_usman', 'Usman Raza', 'usman@gmail.com', 'usman', 'patient', '03001239876', '35202-9876543-3', 34, 'Male', 'Islamabad', 'House 88, Street 14, F-8/2', 'A+', 'None', '03125544332 (Wife)', 'Active', 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=120&h=120&fit=crop'),
('pat_ayesha', 'Ayesha Malik', 'ayesha@gmail.com', 'ayesha', 'patient', '03004456789', '37405-4456789-4', 30, 'Female', 'Rawalpindi', 'House 5, Satellite Town', 'AB+', 'Dust, Sulfa drugs', '03457788990 (Husband)', 'Active', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=120&h=120&fit=crop'),
('pat_asad', 'Asad Ali', 'asad@gmail.com', 'asad', 'patient', '03001112222', '42201-1112222-5', 42, 'Male', 'Lahore', 'House 19, DHA Phase 5', 'O-', 'None', '03012233445 (Son)', 'Active', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&h=120&fit=crop');

-- 2. HOSPITALS TABLE
DROP TABLE IF EXISTS `hospitals`;
CREATE TABLE `hospitals` (
  `id` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(50) DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `license_no` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `location` TEXT NOT NULL,
  `services` ENUM('Covid Test', 'Vaccination', 'Both') NOT NULL DEFAULT 'Both',
  `capacity` INT DEFAULT 100,
  `approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `hospitals` (`id`, `user_id`, `name`, `email`, `password`, `license_no`, `phone`, `city`, `location`, `services`, `capacity`, `approved`) VALUES
('hosp_city', 'usr_hosp_city', 'City Hospital', 'city@hospital.com', 'city', 'REG-HOSP-2024-8891', '03001234500', 'Lahore', 'Downtown Health District, City Center', 'Both', 100, 1),
('hosp_metro', 'usr_hosp_metro', 'Metro Health Clinic', 'metro@hospital.com', 'metro', 'REG-HOSP-2024-5542', '03007654300', 'Karachi', 'North Avenue, Block 4', 'Covid Test', 50, 1),
('hosp_care', 'usr_hosp_care', 'National Care Hospital', 'care@hospital.com', 'care', 'REG-HOSP-2024-3310', '03009876500', 'Islamabad', 'Westside Boulevard, Sector 9', 'Vaccination', 75, 1),
('hosp_apex', 'usr_hosp_apex', 'Apex Medicare Center', 'apex@hospital.com', 'apex', 'REG-HOSP-2025-9921', '03005551122', 'Rawalpindi', 'East Ring Road, Plot 12', 'Both', 40, 0);

-- 3. VACCINES TABLE
DROP TABLE IF EXISTS `vaccines`;
CREATE TABLE `vaccines` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `manufacturer` VARCHAR(150) NOT NULL,
  `total_doses` INT DEFAULT 1000,
  `available_doses` INT DEFAULT 1000,
  `gap_days` INT DEFAULT 28,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `vaccines` (`id`, `name`, `manufacturer`, `total_doses`, `available_doses`, `gap_days`, `status`, `description`) VALUES
('vac_1', 'Covishield', 'Serum Institute', 1000, 650, 28, 'Active', 'Recombinant adenovirus vector vaccine for active immunization.'),
('vac_2', 'Covaxin', 'Bharat Biotech', 800, 420, 28, 'Active', 'Whole-virion inactivated Vero cell vaccine.'),
('vac_3', 'Pfizer-BioNTech', 'Pfizer', 1200, 980, 21, 'Active', 'mRNA vaccine expressing SARS-CoV-2 spike glycoprotein.'),
('vac_4', 'Moderna', 'Moderna', 600, 300, 28, 'Active', 'Lipid nanoparticle-encapsulated mRNA vaccine.'),
('vac_5', 'Sputnik V', 'Gamaleya', 500, 250, 21, 'Inactive', 'Heterologous recombinant adenovirus vector vaccine.'),
('vac_6', 'Sinopharm', 'Sinopharm', 750, 510, 21, 'Active', 'Inactivated whole virus vaccine.');

-- 4. APPOINTMENTS TABLE
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` VARCHAR(50) NOT NULL,
  `patient_id` VARCHAR(50) NOT NULL,
  `patient_name` VARCHAR(150) NOT NULL,
  `patient_email` VARCHAR(150) DEFAULT NULL,
  `patient_phone` VARCHAR(30) DEFAULT NULL,
  `hospital_id` VARCHAR(50) NOT NULL,
  `hospital_name` VARCHAR(150) NOT NULL,
  `service` VARCHAR(100) NOT NULL,
  `date` DATE NOT NULL,
  `time` VARCHAR(30) NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
  `reject_reason` TEXT DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `appointments` (`id`, `patient_id`, `patient_name`, `patient_email`, `patient_phone`, `hospital_id`, `hospital_name`, `service`, `date`, `time`, `status`, `reject_reason`, `message`) VALUES
('apt_101', 'pat_ali', 'Ali Khan', 'ali@gmail.com', '03001234567', 'hosp_city', 'City Hospital', 'Test & Vaccination', '2025-05-12', '10:00 AM', 'Approved', NULL, 'Routine health checkup and Covid verification.'),
('apt_102', 'pat_sana', 'Sana Ahmed', 'sana@gmail.com', '03007654321', 'hosp_city', 'City Hospital', 'Vaccination', '2025-05-12', '11:30 AM', 'Pending', NULL, 'First dose vaccination appointment.'),
('apt_103', 'pat_usman', 'Usman Raza', 'usman@gmail.com', '03001239876', 'hosp_city', 'City Hospital', 'Test', '2025-05-12', '01:00 PM', 'Approved', NULL, 'Need RT-PCR test for international flight.'),
('apt_104', 'pat_ayesha', 'Ayesha Malik', 'ayesha@gmail.com', '03004456789', 'hosp_city', 'City Hospital', 'Vaccination', '2025-05-12', '02:30 PM', 'Approved', NULL, 'Second dose appointment Covishield.'),
('apt_105', 'pat_asad', 'Asad Ali', 'asad@gmail.com', '03001112222', 'hosp_city', 'City Hospital', 'Test & Vaccination', '2025-05-12', '03:30 PM', 'Rejected', 'Slot fully booked. Please select another slot.', 'Mild fever and body pain.'),
('apt_106', 'pat_ali', 'Ali Khan', 'ali@gmail.com', '03001234567', 'hosp_city', 'City Hospital', 'Test & Vaccination', '2025-05-15', '10:00 AM', 'Approved', NULL, 'Follow-up verification.');

-- 5. COVID-19 TESTS TABLE
DROP TABLE IF EXISTS `covid_tests`;
CREATE TABLE `covid_tests` (
  `id` VARCHAR(50) NOT NULL,
  `patient_id` VARCHAR(50) NOT NULL,
  `patient_name` VARCHAR(150) NOT NULL,
  `patient_cnic` VARCHAR(30) DEFAULT NULL,
  `hospital_id` VARCHAR(50) NOT NULL,
  `hospital_name` VARCHAR(150) NOT NULL,
  `test_type` VARCHAR(50) NOT NULL DEFAULT 'RT-PCR',
  `sample_date` DATE NOT NULL,
  `result_date` DATE NOT NULL,
  `result` ENUM('Negative', 'Positive', 'Inconclusive') NOT NULL,
  `ct_value` VARCHAR(100) DEFAULT NULL,
  `lab_id` VARCHAR(100) NOT NULL,
  `doctor` VARCHAR(150) DEFAULT 'Dr. Tariq Mahmood, MD Virology',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `covid_tests` (`id`, `patient_id`, `patient_name`, `patient_cnic`, `hospital_id`, `hospital_name`, `test_type`, `sample_date`, `result_date`, `result`, `ct_value`, `lab_id`, `doctor`, `notes`) VALUES
('test_501', 'pat_ali', 'Ali Khan', '42101-1234567-1', 'hosp_city', 'City Hospital', 'RT-PCR', '2025-05-10', '2025-05-11', 'Negative', '38.5 (Target Not Detected)', 'LAB-PCR-88421', 'Dr. Tariq Mahmood, MD Virology', 'SARS-CoV-2 RNA not detected. Patient cleared for normal activities.'),
('test_502', 'pat_ali', 'Ali Khan', '42101-1234567-1', 'hosp_metro', 'Metro Health Clinic', 'Rapid Antigen', '2025-04-14', '2025-04-14', 'Negative', 'N/A', 'LAB-AG-22019', 'Dr. Farhan Qureshi', 'Antigen test negative for nucleocapsid protein.'),
('test_503', 'pat_usman', 'Usman Raza', '35202-9876543-3', 'hosp_city', 'City Hospital', 'RT-PCR', '2025-05-02', '2025-05-03', 'Positive', '21.4 (High Viral Load)', 'LAB-PCR-90112', 'Dr. Tariq Mahmood, MD Virology', 'SARS-CoV-2 RNA detected. Patient advised 7 days isolation.'),
('test_504', 'pat_sana', 'Sana Ahmed', '42101-7654321-2', 'hosp_metro', 'Metro Health Clinic', 'RT-PCR', '2025-05-04', '2025-05-05', 'Negative', '39.1 (Negative)', 'LAB-PCR-77215', 'Dr. Sarah Bilal', 'Viral genome not detected.');

-- 6. VACCINATION DOSES TABLE
DROP TABLE IF EXISTS `vaccination_doses`;
CREATE TABLE `vaccination_doses` (
  `id` VARCHAR(50) NOT NULL,
  `patient_id` VARCHAR(50) NOT NULL,
  `patient_name` VARCHAR(150) NOT NULL,
  `patient_cnic` VARCHAR(30) DEFAULT NULL,
  `hospital_id` VARCHAR(50) NOT NULL,
  `hospital_name` VARCHAR(150) NOT NULL,
  `vaccine_name` VARCHAR(100) NOT NULL,
  `dose_number` INT NOT NULL DEFAULT 1,
  `total_doses_required` INT DEFAULT 2,
  `dose_date` DATE NOT NULL,
  `next_dose_date` DATE DEFAULT NULL,
  `batch_no` VARCHAR(100) DEFAULT NULL,
  `vaccinator` VARCHAR(150) DEFAULT 'Nurse Fatima Noor',
  `certificate_no` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `vaccination_doses` (`id`, `patient_id`, `patient_name`, `patient_cnic`, `hospital_id`, `hospital_name`, `vaccine_name`, `dose_number`, `total_doses_required`, `dose_date`, `next_dose_date`, `batch_no`, `vaccinator`, `certificate_no`) VALUES
('vacrec_201', 'pat_ali', 'Ali Khan', '42101-1234567-1', 'hosp_city', 'City Hospital', 'Covishield', 1, 2, '2025-05-05', '2025-06-02', 'COV-7749B-IND', 'Nurse Fatima Noor', 'VAC-PK-8890214-ALI'),
('vacrec_202', 'pat_sana', 'Sana Ahmed', '42101-7654321-2', 'hosp_city', 'City Hospital', 'Pfizer-BioNTech', 1, 2, '2025-04-10', '2025-05-01', 'PFZ-99120-USA', 'Nurse Rabia Khan', 'VAC-PK-7741209-SNA'),
('vacrec_203', 'pat_sana', 'Sana Ahmed', '42101-7654321-2', 'hosp_city', 'City Hospital', 'Pfizer-BioNTech', 2, 2, '2025-05-03', NULL, 'PFZ-99144-USA', 'Nurse Rabia Khan', 'VAC-PK-7741209-SNA'),
('vacrec_204', 'pat_ayesha', 'Ayesha Malik', '37405-4456789-4', 'hosp_care', 'National Care Hospital', 'Moderna', 1, 2, '2025-03-01', '2025-03-29', 'MOD-33211', 'Staff Maria', 'VAC-PK-110294-AYS');

-- 7. ROLLBACK REQUESTS TABLE
DROP TABLE IF EXISTS `rollback_requests`;
CREATE TABLE `rollback_requests` (
  `id` VARCHAR(50) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `record_id` VARCHAR(50) NOT NULL,
  `deleted_by` VARCHAR(150) NOT NULL,
  `deleted_on` VARCHAR(50) NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rollback_requests` (`id`, `type`, `record_id`, `deleted_by`, `deleted_on`, `reason`, `status`) VALUES
('rb_1', 'Patient', '15', 'hospital@covid.local', '10 May 2025', 'Deleted by mistake during bulk sync', 'Pending'),
('rb_2', 'Appointment', '182', 'hospital@covid.local', '10 May 2025', 'Wrong entry created with conflicting schedule', 'Pending'),
('rb_3', 'Test', '55', 'hospital@covid.local', '09 May 2025', 'Incorrect lab CT value entered', 'Approved'),
('rb_4', 'Vaccine', '7', 'admin@covid.local', '08 May 2025', 'Duplicate stock batch entry', 'Rejected');

-- 8. AUDIT LOGS TABLE
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` VARCHAR(50) NOT NULL,
  `timestamp` VARCHAR(50) NOT NULL,
  `user` VARCHAR(150) NOT NULL,
  `action` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `audit_logs` (`id`, `timestamp`, `user`, `action`) VALUES
('log_1', '2025-05-12 09:30:15', 'City Hospital', 'Approved Appointment #apt_101 for Ali Khan'),
('log_2', '2025-05-11 15:45:20', 'City Hospital', 'Uploaded RT-PCR Test Result (Negative) for Ali Khan (#test_501)'),
('log_3', '2025-05-10 11:20:00', 'System Administrator', 'Approved Hospital Registration: National Care Hospital'),
('log_4', '2025-05-08 14:10:33', 'System Administrator', 'Updated Covishield Stock (+500 Doses)');
