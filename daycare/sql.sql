-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `daycaresystem`;
CREATE DATABASE `daycaresystem` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `daycaresystem`;

DROP TABLE IF EXISTS `daycare_board`;
CREATE TABLE `daycare_board` (
  `board_id` int(11) NOT NULL AUTO_INCREMENT,
  `board_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `board_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `board_date` date NOT NULL,
  PRIMARY KEY (`board_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_board`;
INSERT INTO `daycare_board` (`board_id`, `board_title`, `board_desc`, `board_date`) VALUES
(9,	'Sport day',	'blah blah blah',	'2017-10-05'),
(10,	'Title 3',	'',	'2017-10-20');

DROP TABLE IF EXISTS `daycare_child`;
CREATE TABLE `daycare_child` (
  `child_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `child_nickname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_fullname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_ic` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_dob` date NOT NULL,
  `child_hobby` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_favfood` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_allergy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `child_emerph` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_emername` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `child_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `child_pic` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`child_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_child`;
INSERT INTO `daycare_child` (`child_id`, `parent_id`, `child_nickname`, `child_fullname`, `child_ic`, `child_dob`, `child_hobby`, `child_favfood`, `child_allergy`, `child_emerph`, `child_emername`, `child_address`, `child_pic`) VALUES
(1,	1,	'Child 1',	'Child 1',	'1234567890',	'0000-00-00',	'',	'',	'',	'',	'',	'',	'');

DROP TABLE IF EXISTS `daycare_foodschedule`;
CREATE TABLE `daycare_foodschedule` (
  `foodschedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `foodschedule_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `foodschedule_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `foodschedule_day` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `foodtitle_id` int(11) NOT NULL,
  `isactive` int(1) NOT NULL,
  PRIMARY KEY (`foodschedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_foodschedule`;
INSERT INTO `daycare_foodschedule` (`foodschedule_id`, `foodschedule_title`, `foodschedule_desc`, `foodschedule_day`, `foodtitle_id`, `isactive`) VALUES
(1,	'Steak',	'Steak for breakfast',	'Monday',	1,	1),
(5,	'Teh C',	'',	'Wednesday',	4,	1),
(6,	'Teh O ice limau',	'',	'Thursday',	4,	1),
(7,	'Pork',	'',	'Friday',	4,	1),
(8,	'Nasi Goreng Kampung',	'',	'Tuesday',	4,	1),
(9,	'Nasi Ayam',	'',	'Monday',	4,	1);

DROP TABLE IF EXISTS `daycare_foodtitle`;
CREATE TABLE `daycare_foodtitle` (
  `foodtitle_id` int(11) NOT NULL AUTO_INCREMENT,
  `foodtitle_title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`foodtitle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_foodtitle`;
INSERT INTO `daycare_foodtitle` (`foodtitle_id`, `foodtitle_title`, `created`) VALUES
(1,	'Breakfast',	'1997-00-00'),
(2,	'Tea Time 1',	'2000-00-00'),
(3,	'Tea Time 2',	'2001-00-00'),
(4,	'Lunch',	'2000-01-01');

DROP TABLE IF EXISTS `daycare_gallery`;
CREATE TABLE `daycare_gallery` (
  `gallery_id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `gallery_date` date NOT NULL,
  PRIMARY KEY (`gallery_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_gallery`;
INSERT INTO `daycare_gallery` (`gallery_id`, `gallery_title`, `gallery_date`) VALUES
(1,	'Gallery 1',	'2017-01-01');

DROP TABLE IF EXISTS `daycare_image`;
CREATE TABLE `daycare_image` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `image_uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gallery_id` int(11) NOT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_image`;
INSERT INTO `daycare_image` (`image_id`, `image_uri`, `gallery_id`) VALUES
(170,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/CUSJ4pzKhlefcq_rcd83voRCb9cf1J_f.jpg',	1),
(171,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/2C4QHvHlbIscWc_s5j4Dm7lUBHOqdv_t.jpg',	1),
(172,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/LI8nMyP7qrqAcN_hCkvWUxnZmLJ7le_s.jpg',	1),
(173,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/Lb17IQVoCpy3GD_oAW1sR7OTZxPWFZ_5.jpg',	1),
(174,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/czKAQnhfWnwWB7_w71NdFUk3G118R4_o.jpg',	1),
(175,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/uzEaLIYngqjQic_deGkUtCk0o6xm8f_w.jpg',	1),
(176,	'http://localhost/fyp-daycare/upload/gallery/Gallery 1/R3vFIQvpuZprMo_ZDQYaK1GJIhgQdz_B.jpg',	1);

DROP TABLE IF EXISTS `daycare_parent`;
CREATE TABLE `daycare_parent` (
  `parent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `parent_phnum` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `parent_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_parent`;
INSERT INTO `daycare_parent` (`parent_id`, `parent_name`, `parent_phnum`, `parent_email`, `user_id`) VALUES
(1,	'Parent 1',	'',	'',	0);

DROP TABLE IF EXISTS `daycare_payment`;
CREATE TABLE `daycare_payment` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_date` date NOT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_payment`;

DROP TABLE IF EXISTS `daycare_paymentline`;
CREATE TABLE `daycare_paymentline` (
  `paymentline_id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentline_item` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `paymentline_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `paymentline_unitprice` decimal(12,2) NOT NULL,
  `paymentline_qty` int(11) NOT NULL,
  `paymentline_total` decimal(12,2) NOT NULL,
  `payment_id` int(11) NOT NULL,
  PRIMARY KEY (`paymentline_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_paymentline`;

DROP TABLE IF EXISTS `daycare_timetable`;
CREATE TABLE `daycare_timetable` (
  `timetable_id` int(11) NOT NULL AUTO_INCREMENT,
  `timetable_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `timetable_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `timetable_day` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `timetable_fromtime` time NOT NULL,
  `timetable_totime` time NOT NULL,
  `isactive` int(1) NOT NULL,
  PRIMARY KEY (`timetable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_timetable`;
INSERT INTO `daycare_timetable` (`timetable_id`, `timetable_title`, `timetable_desc`, `timetable_day`, `timetable_fromtime`, `timetable_totime`, `isactive`) VALUES
(1,	'IT Programming',	'Learn IT from 5 years old',	'Monday',	'08:30:00',	'12:00:00',	1),
(2,	'Hello',	'',	'Wednesday',	'10:30:00',	'11:30:00',	1),
(3,	'Science',	'',	'Tuesday',	'09:00:00',	'11:00:00',	1),
(4,	'Data Structure',	'',	'Monday',	'12:00:00',	'15:00:00',	1),
(11,	'Math Tech 2',	'',	'Thursday',	'11:30:00',	'15:30:00',	1);

DROP TABLE IF EXISTS `daycare_user`;
CREATE TABLE `daycare_user` (
  `user_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_password` varbinary(100) NOT NULL,
  `user_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_permission` int(1) NOT NULL,
  PRIMARY KEY (`user_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `daycare_user`;
INSERT INTO `daycare_user` (`user_ID`, `user_username`, `user_password`, `user_email`, `user_permission`) VALUES
(14,	'test',	UNHEX('97295E514A492D508A5478692A59E1FA'),	'test',	1);

-- 2017-10-08 07:30:39