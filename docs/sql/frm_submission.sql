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

 Date: 08/17/2015 20:57:28 PM
*/

SET NAMES latin1;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `frm_submission`
-- ----------------------------
DROP TABLE IF EXISTS `frm_submission`;
CREATE TABLE `frm_submission` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `form` varchar(63) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ip_address` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `load_timestamp` bigint(20) unsigned NOT NULL,
  `submit_timestamp` bigint(20) unsigned NOT NULL,
  `session_id` char(40) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `nationbuilder_id` bigint(20) unsigned DEFAULT NULL,
  `is_member` tinyint(1) unsigned DEFAULT NULL,
  `utma` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `utmb` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `utmz` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `utmv` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `utmx` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `validate` tinyint(1) unsigned NOT NULL,
  `validate_error_msg` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`),
  KEY `created` (`load_timestamp`),
  KEY `utma` (`utma`),
  KEY `utmb` (`utmb`),
  KEY `utmz` (`utmz`),
  KEY `utmv` (`utmv`),
  KEY `utmx` (`utmx`),
  KEY `form` (`form`),
  KEY `session_id` (`session_id`) USING BTREE,
  KEY `submit_timestamp` (`submit_timestamp`),
  KEY `user_id` (`user_id`),
  KEY `nationbuilder_id` (`nationbuilder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

SET FOREIGN_KEY_CHECKS = 1;
