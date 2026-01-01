-- SQL Dump for Online Exam System Tables
-- Version: No Foreign Keys (Compatible with MyISAM/InnoDB mismatches)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

--
-- Table structure for table `exam_results`
--

CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `correct_answers` int(11) NOT NULL,
  `wrong_answers` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `obtained_marks` decimal(10,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `status` enum('PASS','FAIL') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_schedule_id` (`exam_schedule_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE IF NOT EXISTS `student_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` enum('A','B','C','D') NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_result_id` (`exam_result_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
