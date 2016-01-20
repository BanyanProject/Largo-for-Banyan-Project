/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50624
 Source Host           : localhost
 Source Database       : wpl_bp_trunk_01

 Target Server Type    : MySQL
 Target Server Version : 50624
 File Encoding         : iso-8859-1

 Date: 08/17/2015 22:17:24 PM
*/

SET NAMES latin1;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `frm_contact`
-- ----------------------------
DROP TABLE IF EXISTS `frm_contact`;
CREATE TABLE `frm_contact` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(31) COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `email_signup` tinyint(1) DEFAULT '0',
  `mandrill` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mandrill_error_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mandrill_error_msg` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nationbuilder` tinyint(1) DEFAULT '0',
  `nationbuilder_error` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

SET FOREIGN_KEY_CHECKS = 1;
