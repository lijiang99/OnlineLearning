-- MySQL Script generated by MySQL Workbench
-- 2020年09月24日 星期四 17时07分06秒
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `mydb` ;

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`users_profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`users_profile` ;

CREATE TABLE IF NOT EXISTS `mydb`.`users_profile` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role` ENUM('管理员', '教师', '学生') NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `idcard` VARCHAR(45) NOT NULL,
  `gender` ENUM('男', '女') NOT NULL DEFAULT '男',
  `school` VARCHAR(45) NOT NULL,
  `major` VARCHAR(45) NOT NULL,
  `account` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idcard_UNIQUE` (`idcard` ASC),
  UNIQUE INDEX `account_UNIQUE` (`account` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`teachers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`teachers` ;

CREATE TABLE IF NOT EXISTS `mydb`.`teachers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_profile_id` INT NOT NULL,
  `account` VARCHAR(45) NOT NULL,
  `password` VARCHAR(45) NULL,
  `email` VARCHAR(45) NULL,
  `avatar` VARCHAR(255) NULL,
  `type` ENUM('image/jpg', 'image/png', 'image/gif') NULL,
  `phone` VARCHAR(45) NULL,
  `signature` TEXT NULL,
  `last_logined_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_teachers_users_profile1_idx` (`users_profile_id` ASC),
  UNIQUE INDEX `users_profile_id_UNIQUE` (`users_profile_id` ASC),
  UNIQUE INDEX `account_UNIQUE` (`account` ASC),
  CONSTRAINT `fk_teachers_users_profile1`
    FOREIGN KEY (`users_profile_id`)
    REFERENCES `mydb`.`users_profile` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`courses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`courses` ;

CREATE TABLE IF NOT EXISTS `mydb`.`courses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `teachers_id` INT NOT NULL,
  `title` VARCHAR(45) NOT NULL,
  `cover` VARCHAR(255) NULL,
  `type` ENUM('image/jpg', 'image/png', 'image/gif') NULL,
  `summary` TEXT NULL,
  `status` ENUM('未开始', '已开始', '已结束') NOT NULL DEFAULT '未开始',
  `chapters_count` INT NULL DEFAULT 0,
  `students_count` INT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_courses_teachers1_idx` (`teachers_id` ASC),
  CONSTRAINT `fk_courses_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`chapters`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`chapters` ;

CREATE TABLE IF NOT EXISTS `mydb`.`chapters` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `teachers_id` INT NOT NULL,
  `title` VARCHAR(45) NOT NULL,
  `status` ENUM('未开始', '已开始', '已结束') NOT NULL DEFAULT '未开始',
  `seq` INT NOT NULL DEFAULT 0,
  `tasks_count` INT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_chapters_courses_idx` (`courses_id` ASC),
  INDEX `fk_chapters_teachers1_idx` (`teachers_id` ASC),
  CONSTRAINT `fk_chapters_courses`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_chapters_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`tasks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`tasks` ;

CREATE TABLE IF NOT EXISTS `mydb`.`tasks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `chapters_id` INT NOT NULL,
  `teachers_id` INT NOT NULL,
  `title` VARCHAR(45) NOT NULL,
  `status` ENUM('未开始', '已开始', '已结束') NOT NULL DEFAULT '未开始',
  `seq` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_tasks_courses1_idx` (`courses_id` ASC),
  INDEX `fk_tasks_chapters1_idx` (`chapters_id` ASC),
  INDEX `fk_tasks_teachers1_idx` (`teachers_id` ASC),
  CONSTRAINT `fk_tasks_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_chapters1`
    FOREIGN KEY (`chapters_id`)
    REFERENCES `mydb`.`chapters` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`students`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`students` ;

CREATE TABLE IF NOT EXISTS `mydb`.`students` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `users_profile_id` INT NOT NULL,
  `account` VARCHAR(45) NOT NULL,
  `password` VARCHAR(45) NULL,
  `email` VARCHAR(45) NULL,
  `avatar` VARCHAR(255) NULL,
  `type` ENUM('image/jpg', 'image/png', 'image/gif') NULL,
  `phone` VARCHAR(45) NULL,
  `signature` TEXT NULL,
  `last_logined_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_students_users_profile1_idx` (`users_profile_id` ASC),
  UNIQUE INDEX `users_profile_id_UNIQUE` (`users_profile_id` ASC),
  UNIQUE INDEX `account_UNIQUE` (`account` ASC),
  CONSTRAINT `fk_students_users_profile1`
    FOREIGN KEY (`users_profile_id`)
    REFERENCES `mydb`.`users_profile` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`videos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`videos` ;

CREATE TABLE IF NOT EXISTS `mydb`.`videos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `chapters_id` INT NOT NULL,
  `tasks_id` INT NOT NULL,
  `students_id` INT NOT NULL DEFAULT 0,
  `title` VARCHAR(45) NOT NULL,
  `type` ENUM('mp4', 'avi', 'mkv', 'webm') NOT NULL,
  `media_url` VARCHAR(255) NOT NULL,
  `size` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_resources_courses1_idx` (`courses_id` ASC),
  INDEX `fk_resources_chapters1_idx` (`chapters_id` ASC),
  INDEX `fk_resources_tasks1_idx` (`tasks_id` ASC),
  INDEX `fk_video_resources_students1_idx` (`students_id` ASC),
  CONSTRAINT `fk_resources_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_resources_chapters1`
    FOREIGN KEY (`chapters_id`)
    REFERENCES `mydb`.`chapters` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_resources_tasks1`
    FOREIGN KEY (`tasks_id`)
    REFERENCES `mydb`.`tasks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_video_resources_students1`
    FOREIGN KEY (`students_id`)
    REFERENCES `mydb`.`students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`assignments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`assignments` ;

CREATE TABLE IF NOT EXISTS `mydb`.`assignments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `teachers_id` INT NOT NULL,
  `title` TEXT NOT NULL,
  `status` ENUM('已发布', '待编辑') NOT NULL DEFAULT '待编辑',
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_assignments_courses1_idx` (`courses_id` ASC),
  INDEX `fk_assignments_teachers1_idx` (`teachers_id` ASC),
  CONSTRAINT `fk_assignments_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`question_bank`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`question_bank` ;

CREATE TABLE IF NOT EXISTS `mydb`.`question_bank` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `assignments_id` INT NOT NULL,
  `title` TEXT NOT NULL,
  `option_A` VARCHAR(255) NOT NULL,
  `option_B` VARCHAR(255) NOT NULL,
  `option_C` VARCHAR(255) NOT NULL,
  `option_D` VARCHAR(255) NOT NULL,
  `answers` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `analysis` TEXT NOT NULL,
  `scores` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_question_bank_courses1_idx` (`courses_id` ASC),
  INDEX `fk_question_bank_assignments1_idx` (`assignments_id` ASC),
  CONSTRAINT `fk_question_bank_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_question_bank_assignments1`
    FOREIGN KEY (`assignments_id`)
    REFERENCES `mydb`.`assignments` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`reply`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`reply` ;

CREATE TABLE IF NOT EXISTS `mydb`.`reply` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `assignments_id` INT NOT NULL,
  `question_bank_id` INT NOT NULL,
  `students_id` INT NOT NULL,
  `answers` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `scores` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_reply_assignments1_idx` (`assignments_id` ASC),
  INDEX `fk_reply_students1_idx` (`students_id` ASC),
  INDEX `fk_reply_courses1_idx` (`courses_id` ASC),
  INDEX `fk_reply_question_bank1_idx` (`question_bank_id` ASC),
  CONSTRAINT `fk_reply_assignments1`
    FOREIGN KEY (`assignments_id`)
    REFERENCES `mydb`.`assignments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_reply_students1`
    FOREIGN KEY (`students_id`)
    REFERENCES `mydb`.`students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_reply_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_reply_question_bank1`
    FOREIGN KEY (`question_bank_id`)
    REFERENCES `mydb`.`question_bank` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`error_sets`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`error_sets` ;

CREATE TABLE IF NOT EXISTS `mydb`.`error_sets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reply_id` INT NOT NULL,
  `students_id` INT NOT NULL,
  `title` TEXT NOT NULL,
  `error_answers` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `correct_answers` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `analysis` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_error_sets_reply1_idx` (`reply_id` ASC),
  INDEX `fk_error_sets_students1_idx` (`students_id` ASC),
  UNIQUE INDEX `reply_id_UNIQUE` (`reply_id` ASC),
  CONSTRAINT `fk_error_sets_reply1`
    FOREIGN KEY (`reply_id`)
    REFERENCES `mydb`.`reply` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_error_sets_students1`
    FOREIGN KEY (`students_id`)
    REFERENCES `mydb`.`students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`sign_publish`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`sign_publish` ;

CREATE TABLE IF NOT EXISTS `mydb`.`sign_publish` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `chapters_id` INT NOT NULL,
  `tasks_id` INT NOT NULL,
  `teachers_id` INT NOT NULL,
  `time_start` TIMESTAMP NOT NULL,
  `time_late` TIMESTAMP NOT NULL,
  `time_end` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_signs_courses1_idx` (`courses_id` ASC),
  INDEX `fk_signs_teachers1_idx` (`teachers_id` ASC),
  INDEX `fk_sign_publish_chapters1_idx` (`chapters_id` ASC),
  INDEX `fk_sign_publish_tasks1_idx` (`tasks_id` ASC),
  CONSTRAINT `fk_signs_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_signs_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_sign_publish_chapters1`
    FOREIGN KEY (`chapters_id`)
    REFERENCES `mydb`.`chapters` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_sign_publish_tasks1`
    FOREIGN KEY (`tasks_id`)
    REFERENCES `mydb`.`tasks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`shared_notes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`shared_notes` ;

CREATE TABLE IF NOT EXISTS `mydb`.`shared_notes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `chapters_id` INT NOT NULL,
  `tasks_id` INT NOT NULL,
  `title` VARCHAR(45) NULL,
  `media_url` VARCHAR(255) NOT NULL,
  `size` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_shared_notes_courses1_idx` (`courses_id` ASC),
  INDEX `fk_shared_notes_chapters1_idx` (`chapters_id` ASC),
  INDEX `fk_shared_notes_tasks1_idx` (`tasks_id` ASC),
  CONSTRAINT `fk_shared_notes_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_shared_notes_chapters1`
    FOREIGN KEY (`chapters_id`)
    REFERENCES `mydb`.`chapters` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_shared_notes_tasks1`
    FOREIGN KEY (`tasks_id`)
    REFERENCES `mydb`.`tasks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`classes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`classes` ;

CREATE TABLE IF NOT EXISTS `mydb`.`classes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `class_number` VARCHAR(45) NOT NULL,
  `invite_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_classes_courses1_idx` (`courses_id` ASC),
  UNIQUE INDEX `invite_code_UNIQUE` (`invite_code` ASC),
  CONSTRAINT `fk_classes_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`courses_select`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`courses_select` ;

CREATE TABLE IF NOT EXISTS `mydb`.`courses_select` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `classes_id` INT NOT NULL,
  `students_id` INT NOT NULL,
  `teachers_id` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_courses_select_courses1_idx` (`courses_id` ASC),
  INDEX `fk_courses_select_students1_idx` (`students_id` ASC),
  INDEX `fk_courses_select_teachers1_idx` (`teachers_id` ASC),
  INDEX `fk_courses_select_classes1_idx` (`classes_id` ASC),
  CONSTRAINT `fk_courses_select_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_courses_select_students1`
    FOREIGN KEY (`students_id`)
    REFERENCES `mydb`.`students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_courses_select_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_courses_select_classes1`
    FOREIGN KEY (`classes_id`)
    REFERENCES `mydb`.`classes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`courses_resources`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`courses_resources` ;

CREATE TABLE IF NOT EXISTS `mydb`.`courses_resources` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `chapters_id` INT NOT NULL,
  `tasks_id` INT NOT NULL,
  `teachers_id` INT NOT NULL,
  `title` VARCHAR(45) NOT NULL,
  `type` ENUM('doc', 'docx', 'pdf', 'ppt', 'pptx', 'mp4', 'avi', 'mkv', 'webm') NOT NULL,
  `media_url` VARCHAR(255) NOT NULL,
  `size` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_doc_resources_courses1_idx` (`courses_id` ASC),
  INDEX `fk_doc_resources_chapters1_idx` (`chapters_id` ASC),
  INDEX `fk_doc_resources_tasks1_idx` (`tasks_id` ASC),
  INDEX `fk_doc_resources_teachers1_idx` (`teachers_id` ASC),
  CONSTRAINT `fk_doc_resources_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_doc_resources_chapters1`
    FOREIGN KEY (`chapters_id`)
    REFERENCES `mydb`.`chapters` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_doc_resources_tasks1`
    FOREIGN KEY (`tasks_id`)
    REFERENCES `mydb`.`tasks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_doc_resources_teachers1`
    FOREIGN KEY (`teachers_id`)
    REFERENCES `mydb`.`teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`student_sign`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`student_sign` ;

CREATE TABLE IF NOT EXISTS `mydb`.`student_sign` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sign_publish_id` INT NOT NULL,
  `students_id` INT NOT NULL,
  `sign_at` TIMESTAMP NOT NULL,
  `status` ENUM('已签到', '迟到', '缺课') NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_student_sign_sign_publish1_idx` (`sign_publish_id` ASC),
  INDEX `fk_student_sign_students1_idx` (`students_id` ASC),
  CONSTRAINT `fk_student_sign_sign_publish1`
    FOREIGN KEY (`sign_publish_id`)
    REFERENCES `mydb`.`sign_publish` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_student_sign_students1`
    FOREIGN KEY (`students_id`)
    REFERENCES `mydb`.`students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`temp_question_bank`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`temp_question_bank` ;

CREATE TABLE IF NOT EXISTS `mydb`.`temp_question_bank` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `assignments_id` INT NOT NULL,
  `title` TEXT NOT NULL,
  `option_A` VARCHAR(255) NOT NULL,
  `option_B` VARCHAR(255) NOT NULL,
  `option_C` VARCHAR(255) NOT NULL,
  `option_D` VARCHAR(255) NOT NULL,
  `answers` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `analysis` TEXT NOT NULL,
  `scores` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_temp_assignments_courses1_idx` (`courses_id` ASC),
  INDEX `fk_temp_question_bank_assignments1_idx` (`assignments_id` ASC),
  CONSTRAINT `fk_temp_assignments_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_temp_question_bank_assignments1`
    FOREIGN KEY (`assignments_id`)
    REFERENCES `mydb`.`assignments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`test_scores`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`test_scores` ;

CREATE TABLE IF NOT EXISTS `mydb`.`test_scores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `classes_id` INT NOT NULL,
  `assignments_id` INT NOT NULL,
  `students_id` INT NOT NULL,
  `scores` INT NOT NULL,
  `total_scores` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_scores_courses1_idx` (`courses_id` ASC),
  INDEX `fk_scores_students1_idx` (`students_id` ASC),
  INDEX `fk_test_scores_assignments1_idx` (`assignments_id` ASC),
  INDEX `fk_test_scores_classes1_idx` (`classes_id` ASC),
  CONSTRAINT `fk_scores_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_scores_students1`
    FOREIGN KEY (`students_id`)
    REFERENCES `mydb`.`students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_test_scores_assignments1`
    FOREIGN KEY (`assignments_id`)
    REFERENCES `mydb`.`assignments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_test_scores_classes1`
    FOREIGN KEY (`classes_id`)
    REFERENCES `mydb`.`classes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`assignments_to_class`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`assignments_to_class` ;

CREATE TABLE IF NOT EXISTS `mydb`.`assignments_to_class` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courses_id` INT NOT NULL,
  `assignments_id` INT NOT NULL,
  `classes_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_assignments_to_class_assignments1_idx` (`assignments_id` ASC),
  INDEX `fk_assignments_to_class_classes1_idx` (`classes_id` ASC),
  INDEX `fk_assignments_to_class_courses1_idx` (`courses_id` ASC),
  CONSTRAINT `fk_assignments_to_class_assignments1`
    FOREIGN KEY (`assignments_id`)
    REFERENCES `mydb`.`assignments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_to_class_classes1`
    FOREIGN KEY (`classes_id`)
    REFERENCES `mydb`.`classes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_assignments_to_class_courses1`
    FOREIGN KEY (`courses_id`)
    REFERENCES `mydb`.`courses` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
