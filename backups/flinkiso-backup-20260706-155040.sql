-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: flinkiso
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `flinkiso`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `flinkiso` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;

USE `flinkiso`;

--
-- Table structure for table `approval_comments`
--

DROP TABLE IF EXISTS `approval_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_comments` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `approval_id` varchar(36) NOT NULL,
  `from` varchar(36) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `response_status` int(1) DEFAULT 0 COMMENT '0=open, 1=responded',
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_comments`
--

LOCK TABLES `approval_comments` WRITE;
/*!40000 ALTER TABLE `approval_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_processes`
--

DROP TABLE IF EXISTS `approval_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_processes` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Process Title',
  `process_description` text NOT NULL,
  `applicable_to` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `approval_step_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_processes`
--

LOCK TABLES `approval_processes` WRITE;
/*!40000 ALTER TABLE `approval_processes` DISABLE KEYS */;
INSERT INTO `approval_processes` VALUES ('1e03dd49-d903-4946-8e02-ce5a54c79cc0',1,'General Approval Process','General Approval Process for custom html form','[\"28eb2068-cf9d-4dce-8ea6-2347e14b3d60\",\"b2636904-8dff-4356-a06a-4887341c8def\",\"06ab4495-44dc-48b3-86da-61f67f492cb7\",\"b5eaa2ea-e624-4028-887c-c55d3dc1cece\",\"e60b9ebd-6a04-4caa-975d-c7882a65a704\",\"ac69bfd7-9b0a-4cc8-8708-5fd9a015d16e\",\"795b0c71-a7dc-4559-bfb3-23be11d735d2\",\"a41faf5d-4748-4c3c-8de2-03b91630a93d\",\"0d35f561-262a-4620-903e-61eb6e9a683a\"]',NULL,0,NULL,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6'),('3233d6a8-eb16-412f-bf9f-bd3189aa491b',2,'Document Approval Process','Every use must send the newly created document to 1. Reviewer 2. Approver 3. Publisher.','[\"qc_documents\"]',NULL,0,NULL,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6');
/*!40000 ALTER TABLE `approval_processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_steps`
--

DROP TABLE IF EXISTS `approval_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_steps` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `approval_process_id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Process Title',
  `process_step` int(11) NOT NULL,
  `send_to_department_hod` tinyint(1) DEFAULT NULL,
  `send_to_designation` varchar(36) DEFAULT NULL,
  `send_to_admins` tinyint(1) DEFAULT NULL,
  `send_to_reviwers` tinyint(1) DEFAULT NULL,
  `send_to_publishers` tinyint(1) DEFAULT NULL,
  `send_to_approvers` tinyint(1) DEFAULT NULL,
  `send_to_users` text DEFAULT NULL,
  `ignore_department` tinyint(1) DEFAULT 0,
  `ignore_branch` tinyint(1) DEFAULT 0,
  `comments` text DEFAULT NULL,
  `approval_mode` int(11) DEFAULT 0 COMMENT '0=view-only, 1=edit',
  `approval_type` int(11) DEFAULT 0 COMMENT '0=all, 1=any',
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_steps`
--

LOCK TABLES `approval_steps` WRITE;
/*!40000 ALTER TABLE `approval_steps` DISABLE KEYS */;
INSERT INTO `approval_steps` VALUES ('09b4002a-2844-4296-83bf-d94ff1324715',1,'1e03dd49-d903-4946-8e02-ce5a54c79cc0','Step-1',1,0,'-1',0,1,0,0,NULL,1,1,'Send record to reviewers',1,1,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6'),('bfbb5923-e640-4c74-897d-8766436e5f25',2,'1e03dd49-d903-4946-8e02-ce5a54c79cc0','Step-2',2,1,'-1',0,0,0,0,NULL,1,1,'Send record to HoD',1,1,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6'),('472691d7-9029-4412-ab96-2eba62fe39d9',6,'3233d6a8-eb16-412f-bf9f-bd3189aa491b','Send to Reviewer',1,0,'-1',0,1,0,0,NULL,1,1,'Send to Reviewer',0,1,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6'),('ce570632-4161-457b-8877-2f1ab736b069',7,'3233d6a8-eb16-412f-bf9f-bd3189aa491b','Send to Approver',2,0,'-1',0,0,0,1,NULL,1,1,'Send to Approver',0,1,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6'),('fcd066eb-e26a-43f5-8eda-8944ddf0f336',8,'3233d6a8-eb16-412f-bf9f-bd3189aa491b','Send to Publisher',3,0,'-1',0,0,1,0,NULL,1,1,'Send to Publisher',1,1,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6'),('50778587-df56-4f21-b029-638025d0a5b9',9,'63440009-9f32-4d60-81c2-f979918e2b27','Step -1',1,1,'-1',0,0,0,0,NULL,1,1,'Send to Hod',1,1,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','8df2f194-4937-45cb-babc-d2a949df4f08',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','a50c7ec8-d59c-4095-8297-5cad825f8aa6');
/*!40000 ALTER TABLE `approval_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approvals`
--

DROP TABLE IF EXISTS `approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approvals` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Approval Title',
  `model_name` varchar(250) NOT NULL,
  `controller_name` varchar(250) NOT NULL,
  `record` varchar(36) NOT NULL,
  `from` varchar(36) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `approval_step` int(2) DEFAULT NULL,
  `status` varchar(120) DEFAULT NULL,
  `approval_mode` int(1) DEFAULT 0 COMMENT '0=view-only, 1=edit',
  `approval_type` int(1) DEFAULT 0 COMMENT '0=all, 1=any',
  `approval_cycle` int(11) DEFAULT 0,
  `approval_status` int(1) DEFAULT 0 COMMENT '0=open, 1=approved 2=reject',
  `approver_comments` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approvals`
--

LOCK TABLES `approvals` WRITE;
/*!40000 ALTER TABLE `approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `departments` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES ('fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc',1,'Lahore',NULL,'[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','0',NULL,NULL,'2026-07-06 16:11:37','0',NULL);
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chd_mrm0_child_1_v1s`
--

DROP TABLE IF EXISTS `chd_mrm0_child_1_v1s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chd_mrm0_child_1_v1s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `current_status` int(1) DEFAULT NULL,
  `closure_comments` text DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `assigned_to` varchar(36) DEFAULT NULL,
  `agenda_details` varchar(255) DEFAULT NULL,
  `audit_number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT 'ea17ca9b-c5ec-4f0d-ad7c-14f534b5f32d',
  `custom_table_id` varchar(36) NOT NULL DEFAULT '2fcdbccf-b34b-467e-957e-da9a1eb934e2',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chd_mrm0_child_1_v1s`
--

LOCK TABLES `chd_mrm0_child_1_v1s` WRITE;
/*!40000 ALTER TABLE `chd_mrm0_child_1_v1s` DISABLE KEYS */;
/*!40000 ALTER TABLE `chd_mrm0_child_1_v1s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clauses`
--

DROP TABLE IF EXISTS `clauses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clauses` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `standard` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `standard_id` varchar(36) NOT NULL,
  `clause` varchar(8) NOT NULL,
  `sub-clause` varchar(120) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `additional_details` text DEFAULT NULL,
  `tabs` text DEFAULT NULL,
  `external_link_1` varchar(255) DEFAULT NULL,
  `external_link_2` varchar(255) DEFAULT NULL,
  `external_link_3` varchar(255) DEFAULT NULL,
  `external_link_4` varchar(255) DEFAULT NULL,
  `external_link_5` varchar(255) DEFAULT NULL,
  `system_tables` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=3846 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clauses`
--

LOCK TABLES `clauses` WRITE;
/*!40000 ALTER TABLE `clauses` DISABLE KEYS */;
INSERT INTO `clauses` VALUES ('33457b87-110e-4bba-bf46-24f34952d44b',1,'Scope','2015','58511238-fba8-4db9-aad0-833fc20b8995','1','','<p>add scope</p>\r\n<p><strong>Adding new changes to the Scope.</strong></p>','','','','','','','',NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','a3d53ee2-adcd-4433-9363-87aed5be038c',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id'),('f7c60cc8-5e18-4107-acd6-091b02939554',2,'Normative references','2015','58511238-fba8-4db9-aad0-833fc20b8995','2','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('250b58fb-3570-465a-ab3a-88519ed89edc',3,'Terms and Definitions','2015','58511238-fba8-4db9-aad0-833fc20b8995','3','','<p>Click on edit button to update this section.</p>','','','','','','','',NULL,NULL,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id'),('0810bbab-4c7c-4e40-9ee6-d19c336b7528',4,'Context of the organization','2015','58511238-fba8-4db9-aad0-833fc20b8995','4','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('477653e6-09ee-43a7-a586-c9e996cb9241',5,'Understanding Context of the Organization','2015','58511238-fba8-4db9-aad0-833fc20b8995','4','4.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('80eb0cb7-6d76-4d56-8728-9037d19874ce',6,'Understanding the needs and expectations of interested parties','2015','58511238-fba8-4db9-aad0-833fc20b8995','4','4.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('039d000a-8400-4b14-b113-ff555ef3e491',7,'Quality management system and its processes','2015','58511238-fba8-4db9-aad0-833fc20b8995','4','4.4','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('feebded0-121d-4a3f-bc65-8c867677686b',8,'Leadership','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('0ff63756-7898-42fe-82c6-02a8e89a34da',9,'Leadership and commitment','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','5.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('282667ae-f4a1-4738-89ae-73c4e1533fb6',10,'Leadership And Commitment For The Quality Management System','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','5.1.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('1d11ae04-059b-4286-954a-f06a9826ce4a',11,'Customer Focus','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','5.1.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('d6bebd89-0ff2-4f1f-9846-f001cd01a5cc',12,'Policy','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','5.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('1144448a-6125-4517-b329-ca68e941a6a0',13,'Establishing the quality policy','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','5.2.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('887984b1-65dc-43c4-b361-4fa770e8c05b',14,'Communicating the quality policy','2015','58511238-fba8-4db9-aad0-833fc20b8995','5','5.2.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('cfde6b48-6e46-45c9-9d54-96d807dedec3',15,'Planning','2015','58511238-fba8-4db9-aad0-833fc20b8995','6','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('eba0952e-52c2-4bbd-a9ca-19df876b2e34',16,'Actions to address risks and opportunities','2015','58511238-fba8-4db9-aad0-833fc20b8995','6','6.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('3fe227cd-543d-468e-aa80-6a2b5b18dd63',17,'Quality objectives and planning to achieve them','2015','58511238-fba8-4db9-aad0-833fc20b8995','6','6.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('ffe8d9ac-52b0-4fe8-a9e5-c0cf3c85a2c4',18,'Planning of changes','2015','58511238-fba8-4db9-aad0-833fc20b8995','6','6.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('9b7a6dee-2bbc-42d5-b874-612f63ce9e04',19,'Support','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('98d225bb-dd85-426f-b393-f46b35de4971',20,'Resources','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('c3a6f9a9-564d-4d80-8259-c85060e615fa',21,'General','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('db2eba0c-7cd2-451d-863f-64ad6f8282d8',22,'People','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('3083465f-044f-4d06-afbc-0dbb2bfc977e',23,'Infrastructure','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('c887821c-f01c-44a6-b045-fbc0ea99bcff',24,'Environment for the operation of processes','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1.4','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('51aa0728-a93b-4f31-b035-1d28146e2dee',25,'Monitoring and measuring resources','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1.5','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('795a904c-c569-4ce9-9f12-09ae100716c9',26,'Organizational knowledge','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.1.6','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('496d4be4-b336-4fe7-84b8-037a5ebc833a',27,'Competence','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('b5e6bdae-e15d-4fce-83f0-55b6adf864c3',28,'Awareness','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('597ea05c-3322-43ac-ad37-afc84d16b08b',29,'Communication','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.4','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('7b2b7ba9-4163-425f-88c2-c23c64a66e82',30,'Documented information','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.5','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('a97065be-58d8-44c2-bc1a-01307a810c66',31,'General','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.5.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('c713d46d-a0c8-4a89-9919-87e429a88945',32,'Creating and updating documented information','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.5.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('ca564977-1dbb-4df6-a558-7db23297b063',33,'Control of documented information','2015','58511238-fba8-4db9-aad0-833fc20b8995','7','7.5.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('1717b03b-218a-4772-a7d8-c7f7a422bb09',34,'Operation','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('ac91acb1-e3c8-4aa4-b789-ae6c0fbc663c',35,'Operational planning and control','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','8.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('f7ba6e3f-e5a5-4c8f-a767-1ec48ae2a31b',36,'Requirements for products and services','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','8.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('986a6cbc-17ea-46a2-afc4-4af9c5ee69cb',37,'Design and development of products and services','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','8.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('2169385d-e42e-4ede-98de-12b3ed4eb957',38,'Product and service provision','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','8.5','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('b715aae0-a3a4-4674-93db-7757f0c0b728',39,'Release of products and services','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','8.6','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('c4c1b0f3-5981-439f-b8ae-d62fef03f03c',40,'Control of nonconforming outputs','2015','58511238-fba8-4db9-aad0-833fc20b8995','8','8.7','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('d6174ce2-2a50-4388-bb94-9ef2c8989d78',41,'Performance evaluation','2015','58511238-fba8-4db9-aad0-833fc20b8995','9','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('9dea3f9d-39af-4ff0-979c-75b48e111d4b',42,'Customer Satisfaction','2015','58511238-fba8-4db9-aad0-833fc20b8995','9','9.1.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('12b19b1e-635c-4a2e-8556-93ab681b5c4e',43,'Internal Audit','2015','58511238-fba8-4db9-aad0-833fc20b8995','9','9.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('f6b9a9f0-1005-4825-a37e-2bf147a54bee',44,'Management Review','2015','58511238-fba8-4db9-aad0-833fc20b8995','9','9.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('01f0f02d-4da7-467a-b165-010f8aed86f0',45,'Improvement','2015','58511238-fba8-4db9-aad0-833fc20b8995','10','','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('47672c9e-9453-4550-99e5-8a251b258ea3',46,'General','2015','58511238-fba8-4db9-aad0-833fc20b8995','10','10.1','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('cfb499b2-1c86-41f2-9816-6c51905d88b5',47,'Nonconformity in ISO 9001','2015','58511238-fba8-4db9-aad0-833fc20b8995','10','10.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('2210da76-0fe8-4e57-88db-bcf2dbe898a4',48,'What is Non-conformance?','2015','58511238-fba8-4db9-aad0-833fc20b8995','10','10.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('ff1e1c9a-6135-4c6f-be59-1d5a3a270370',49,'Corrective Action','2015','58511238-fba8-4db9-aad0-833fc20b8995','10','10.2','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('52e6ee36-582b-4c44-a883-0c181659ea44',50,'Continual Improvement','2015','58511238-fba8-4db9-aad0-833fc20b8995','10','10.3','Click on edit button to update this section.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('2f12892b-9264-48e7-848e-ef98ad0537ce',3844,'scope 2','abc','712a4904-6cbe-4e5c-a616-5fd777a60037','2','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,1,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('7e65ab67-a561-46b3-965b-946e3c3d1f67',3845,'Change Clause Title','abc','712a4904-6cbe-4e5c-a616-5fd777a60037','2','2','<p>Add clause details.&nbsp;</p>','','','','','','','',NULL,0,0,NULL,1,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id'),('d251419a-ef47-432a-9038-f511e12ab1b6',3843,'Scope','abc','712a4904-6cbe-4e5c-a616-5fd777a60037','1','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,1,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37','0','update_comany_id');
/*!40000 ALTER TABLE `clauses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(225) NOT NULL,
  `description` text NOT NULL,
  `logo` int(1) DEFAULT 0 COMMENT '0 = default, 1 = custom logo',
  `company_logo` varchar(225) DEFAULT NULL,
  `number_of_branches` int(1) NOT NULL,
  `allow_multiple_login` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0= Not allow, 1=Allow',
  `limit_login_attempt` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0= No limit, 1= limit upto 3 attempt',
  `flinkiso_start_date` date NOT NULL,
  `flinkiso_end_date` date NOT NULL,
  `welcome_message` text DEFAULT NULL,
  `quality_policy` text DEFAULT NULL,
  `vision_statement` text DEFAULT NULL,
  `mission_statement` text DEFAULT NULL,
  `scope_of_qms` text DEFAULT NULL,
  `schedule_id` varchar(36) DEFAULT NULL,
  `smtp_setup` tinyint(1) DEFAULT 0,
  `is_smtp` tinyint(1) DEFAULT 0,
  `liscence_key` varchar(36) DEFAULT NULL,
  `sample_data` tinyint(1) NOT NULL DEFAULT 0,
  `audit_plan` text DEFAULT NULL,
  `activate_password_setting` int(1) NOT NULL DEFAULT 0,
  `two_way_authentication` tinyint(1) DEFAULT NULL,
  `dir_name` varchar(50) NOT NULL,
  `timezone` varchar(90) DEFAULT NULL,
  `version` float(11,2) DEFAULT NULL,
  `change_management_table` varchar(36) DEFAULT NULL,
  `change_management_table_fields` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(4) DEFAULT 0,
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `division_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES ('11111111-1111-1111-1111-111111111111',1,'FlinkISO Dev','<p></p>',0,NULL,1,0,1,'2026-07-06','2026-08-06','<p></p>',NULL,NULL,NULL,NULL,NULL,1,0,'DEV-LOCAL-KEY',0,NULL,0,NULL,'FlinkISO Dev','Asia/Karachi',NULL,NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','0',NULL,NULL,'2026-07-06 16:11:37','0','0',NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_codes`
--

DROP TABLE IF EXISTS `custom_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_codes` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `custom_table_id` varchar(36) NOT NULL,
  `name` varchar(120) NOT NULL,
  `css` text DEFAULT NULL,
  `js` text DEFAULT NULL,
  `custom_code` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_codes`
--

LOCK TABLES `custom_codes` WRITE;
/*!40000 ALTER TABLE `custom_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_files`
--

DROP TABLE IF EXISTS `custom_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_files` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `record` varchar(36) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(5) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `employee_id` varchar(36) NOT NULL,
  `action` int(1) DEFAULT 0 COMMENT '0=Download, 1=Delete',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_files`
--

LOCK TABLES `custom_files` WRITE;
/*!40000 ALTER TABLE `custom_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_table_processes`
--

DROP TABLE IF EXISTS `custom_table_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_table_processes` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `custom_table_id` varchar(36) NOT NULL,
  `process_id` varchar(36) NOT NULL,
  `sequence` int(11) NOT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_table_processes`
--

LOCK TABLES `custom_table_processes` WRITE;
/*!40000 ALTER TABLE `custom_table_processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_table_processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_table_tasks`
--

DROP TABLE IF EXISTS `custom_table_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_table_tasks` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `custom_table_id` varchar(36) NOT NULL,
  `employee_field` varchar(250) NOT NULL,
  `condition_field` varchar(250) NOT NULL,
  `condition` varchar(10) NOT NULL,
  `csvoption` int(1) DEFAULT 0,
  `date_field` varchar(250) NOT NULL,
  `message` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_table_tasks`
--

LOCK TABLES `custom_table_tasks` WRITE;
/*!40000 ALTER TABLE `custom_table_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_table_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_tables`
--

DROP TABLE IF EXISTS `custom_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_tables` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  `default_field` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `table_name` varchar(255) NOT NULL,
  `table_version` int(11) NOT NULL,
  `table_type` int(1) NOT NULL DEFAULT 0 COMMENT '0=doc;1=process',
  `file_key` varchar(50) DEFAULT NULL,
  `version_keys` text DEFAULT NULL,
  `file_status` int(1) DEFAULT NULL,
  `last_saved` datetime DEFAULT NULL,
  `qc_document_id` varchar(36) DEFAULT NULL,
  `field_name` varchar(255) DEFAULT NULL,
  `field_value` int(1) DEFAULT NULL,
  `process_id` varchar(36) DEFAULT NULL,
  `custom_table_id` varchar(36) DEFAULT NULL,
  `display_field` int(1) NOT NULL DEFAULT 0,
  `fields` text DEFAULT NULL,
  `belongs_to` text DEFAULT NULL,
  `has_many` text DEFAULT NULL,
  `child_tables_fields` text DEFAULT NULL,
  `form_layout` int(1) NOT NULL DEFAULT 2 COMMENT '1=regular,2=table',
  `add_form_script` text DEFAULT NULL,
  `edit_form_script` text DEFAULT NULL,
  `branches` text DEFAULT NULL,
  `departments` text DEFAULT NULL,
  `designations` text DEFAULT NULL,
  `users` text DEFAULT NULL,
  `creators` text DEFAULT NULL,
  `editors` text DEFAULT NULL,
  `viewers` text DEFAULT NULL,
  `approvers` text DEFAULT NULL,
  `approval_process_id` varchar(36) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `table_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=locked 1=unlocked',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_tables`
--

LOCK TABLES `custom_tables` WRITE;
/*!40000 ALTER TABLE `custom_tables` DISABLE KEYS */;
INSERT INTO `custom_tables` VALUES ('0e7f279f-2a4d-414e-b103-a40f40983189',1,'581c1bb5db15645d76a7e672f882ac71','0','Audit Catetory',NULL,'tbl_audit_catetory_v0s',0,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'[{\"dummy\":\"\",\"field_name\":\"name\",\"field_label\":\"Name\",\"old_field_name\":\"name\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"mandatory\":\"1\",\"default_field\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"show_comments\":\"\"}]',NULL,NULL,NULL,0,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('28eb2068-cf9d-4dce-8ea6-2347e14b3d60',2,'581c1bb5db15645d76a7e672f882ac71',NULL,'Audit Schedule','','tbl_audit_schedule_0_v0s',0,0,'1260018022',NULL,NULL,NULL,'c29a4cae-ee08-4dc8-9e30-ba6bce0005b4',NULL,NULL,'','',0,'[{\"dummy\":\"\",\"field_name\":\"audit_number\",\"field_label\":\"QXVkaXQgTnVtYmVy\",\"old_field_name\":\"audit_number\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"text\",\"mandatory\":\"1\",\"default_field\":\"1\",\"is_unique\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"show_last_value\":\"1\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"-1\",\"field_name\":\"standard\",\"field_label\":\"U3RhbmRhcmQ=\",\"old_field_name\":\"standard\",\"linked_to\":\"Standards\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"4\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"showdocs\":\"\",\"showdocs_mode\":\"\",\"showdocs_copy\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"-1\",\"field_name\":\"audit_category\",\"field_label\":\"QXVkaXQgQ2F0ZWdvcnk=\",\"old_field_name\":\"audit_category\",\"linked_to\":\"TblAuditCatetoryV0\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"4\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"showdocs\":\"0\",\"showdocs_mode\":\"\",\"showdocs_copy\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"schedule_start_date\",\"field_label\":\"U2NoZWR1bGUgU3RhcnQgRGF0ZQ==\",\"old_field_name\":\"schedule_start_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"1\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"\",\"field_name\":\"scheduled_end_date\",\"field_label\":\"U2NoZWR1bGVkIEVuZCBEYXRl\",\"old_field_name\":\"scheduled_end_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"1\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"schedule_start_date\"},{\"field_name\":\"audit_locations\",\"field_label\":\"QXVkaXQgTG9jYXRpb25z\",\"old_field_name\":\"audit_locations\",\"linked_to\":\"Branches\",\"display_type\":\"4\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"dropdown-m\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"field_name\":\"departments_to_be_audited\",\"field_label\":\"RGVwYXJ0bWVudHMgVG8gQmUgQXVkaXRlZA==\",\"old_field_name\":\"departments_to_be_audited\",\"linked_to\":\"Departments\",\"display_type\":\"4\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"dropdown-m\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"0\",\"field_name\":\"current_status\",\"field_label\":\"Q3VycmVudCBTdGF0dXM=\",\"old_field_name\":\"current_status\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"12\",\"data_type\":\"radio\",\"csvoptions\":\"scheduled,on-going,completed,cancled\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"notes\",\"field_label\":\"Tm90ZXM=\",\"old_field_name\":\"notes\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"}]','{\"standard\":\"Standards\",\"audit_category\":\"TblAuditCatetoryV0\",\"audit_locations\":\"Branches\",\"departments_to_be_audited\":\"Departments\"}','',NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','d896c27a-567c-46e2-948c-49a801271a9b',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('b2636904-8dff-4356-a06a-4887341c8def',3,'581c1bb5db15645d76a7e672f882ac71',NULL,'Audit Checklist','','tbl_audit_checklist_0_v0s',0,0,'3808101762',NULL,NULL,NULL,'cdf45c35-4a25-4cc9-bec7-4692f40d27af','current_status',0,'','',0,'[{\"dummy\":\"\",\"field_name\":\"checklist_title\",\"field_label\":\"Q2hlY2tsaXN0IFRpdGxl\",\"old_field_name\":\"checklist_title\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"mandatory\":\"0\",\"default_field\":\"1\",\"is_unique\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"show_last_value\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"date_added\",\"field_label\":\"RGF0ZSBBZGRlZA==\",\"old_field_name\":\"date_added\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"1\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"-1\",\"field_name\":\"added_by\",\"field_label\":\"QWRkZWQgQnk=\",\"old_field_name\":\"added_by\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_signature\":\"1\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"comments\",\"field_label\":\"Q29tbWVudHM=\",\"old_field_name\":\"comments\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"}]','{\"added_by\":\"Employees\"}','',NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','d896c27a-567c-46e2-948c-49a801271a9b',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('83c439cf-e6ab-42de-b1c8-a941a4ba18f7',4,'581c1bb5db15645d76a7e672f882ac71',NULL,'Audit Findings','','tbl_audit_findings_0_v0s',0,0,'2575069392',NULL,NULL,NULL,'5abc3b5b-d7e5-4252-8fe2-d21200c26556','current_status',1,'','',0,'[{\"dummy\":\"\",\"field_name\":\"finding_number\",\"field_label\":\"RmluZGluZyBOdW1iZXI=\",\"old_field_name\":\"finding_number\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"3\",\"data_type\":\"text\",\"mandatory\":\"1\",\"default_field\":\"1\",\"is_unique\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"show_last_value\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"audit_start_date\",\"field_label\":\"QXVkaXQgU3RhcnQgRGF0ZQ==\",\"old_field_name\":\"audit_start_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"6\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"date\",\"mandatory\":\"1\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"\",\"field_name\":\"audit_end_date\",\"field_label\":\"QXVkaXQgRW5kIERhdGU=\",\"old_field_name\":\"audit_end_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"5\",\"data_type\":\"date\",\"mandatory\":\"1\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"-1\",\"field_name\":\"auditor\",\"field_label\":\"QXVkaXRvcg==\",\"old_field_name\":\"auditor\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_signature\":\"1\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"-1\",\"field_name\":\"auditee\",\"field_label\":\"QXVkaXRlZQ==\",\"old_field_name\":\"auditee\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_signature\":\"1\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"0\",\"field_name\":\"finding_type\",\"field_label\":\"RmluZGluZyBUeXBl\",\"old_field_name\":\"finding_type\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"6\",\"data_type\":\"radio\",\"csvoptions\":\"Observation, Non-conformity\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"0\",\"field_name\":\"current_status\",\"field_label\":\"Q3VycmVudCBTdGF0dXM=\",\"old_field_name\":\"current_status\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"6\",\"data_type\":\"radio\",\"csvoptions\":\"Open,Closed\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"findings\",\"field_label\":\"RmluZGluZ3M=\",\"old_field_name\":\"findings\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\\\\\"\\\\\\\\\\\\\\\"[\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\"auditor\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\"]\\\\\\\\\\\\\\\"\\\\\\\"\\\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"response_from_auditee\",\"field_label\":\"UmVzcG9uc2UgRnJvbSBBdWRpdGVl\",\"old_field_name\":\"response_from_auditee\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"1\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"}]','{\"auditor\":\"Employees\",\"auditee\":\"Employees\"}','',NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','d896c27a-567c-46e2-948c-49a801271a9b',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('d9385db9-54d3-4569-b625-7507181d00d5',5,'581c1bb5db15645d76a7e672f882ac71',NULL,'MRM','','tbl_mrm_0_v0s',0,0,'441572600',NULL,NULL,NULL,'ea17ca9b-c5ec-4f0d-ad7c-14f534b5f32d',NULL,NULL,'','',0,'[{\"dummy\":\"\",\"field_name\":\"meeting_number\",\"field_label\":\"TWVldGluZyBOdW1iZXI=\",\"old_field_name\":\"meeting_number\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"text\",\"mandatory\":\"1\",\"default_field\":\"1\",\"is_unique\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"0\",\"show_last_value\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"scheduled_date_time\",\"field_label\":\"U2NoZWR1bGVkIERhdGUgVGltZQ==\",\"old_field_name\":\"scheduled_date_time\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"6\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"datetime\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"1\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"-1\",\"field_name\":\"proposed_by\",\"field_label\":\"UHJvcG9zZWQgQnk=\",\"old_field_name\":\"proposed_by\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"4\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"2\",\"add_signature\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"meeting_details\",\"field_label\":\"TWVldGluZyBEZXRhaWxz\",\"old_field_name\":\"meeting_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"3\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"field_name\":\"invitees\",\"field_label\":\"SW52aXRlZXM=\",\"old_field_name\":\"invitees\",\"linked_to\":\"Employees\",\"display_type\":\"4\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"dropdown-m\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"4\",\"add_signature\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"0\",\"field_name\":\"meeting_status\",\"field_label\":\"TWVldGluZyBTdGF0dXM=\",\"old_field_name\":\"meeting_status\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"12\",\"data_type\":\"radio\",\"csvoptions\":\"Scheduled,Conducted,Cancled\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"6\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"field_name\":\"comments5\",\"show_comments\":\"RGF0YSB0byBiZSBhZGRlZCBhZnRlciBtZWV0aW5n\",\"size\":\"12\",\"display_type\":\"7\",\"field_type\":\"0\",\"data_type\":\"comments\",\"index_show\":\"0\",\"sequence\":\"7\",\"linked_to\":\"-1\",\"dummy\":\"0\",\"drop\":\"0\",\"old_field_name\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"field_label\":\"\"},{\"dummy\":\"\",\"field_name\":\"actual_meeting_date_time\",\"field_label\":\"QWN0dWFsIE1lZXRpbmcgRGF0ZSBUaW1l\",\"old_field_name\":\"actual_meeting_date_time\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"6\",\"length\":\"255\",\"size\":\"5\",\"data_type\":\"datetime\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"8\",\"add_disabled\":\"1\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"field_name\":\"attainted_by\",\"field_label\":\"QXR0YWludGVkIEJ5\",\"old_field_name\":\"attainted_by\",\"linked_to\":\"Employees\",\"display_type\":\"4\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"7\",\"data_type\":\"dropdown-m\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"9\",\"add_signature\":\"0\",\"add_disabled\":\"1\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"}]','{\"proposed_by\":\"Employees\",\"invitees\":\"Employees\",\"attainted_by\":\"Employees\"}','',NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','d896c27a-567c-46e2-948c-49a801271a9b',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('06ab4495-44dc-48b3-86da-61f67f492cb7',6,'581c1bb5db15645d76a7e672f882ac71',NULL,'Customer Details','','tbl_customer_details_0_v0s',0,0,'1668503338',NULL,NULL,NULL,'06d28e70-8ed6-424d-8a8c-deb73f5f5510',NULL,NULL,NULL,NULL,0,'[{\"dummy\":\"\",\"field_name\":\"customer_name\",\"field_label\":\"Q3VzdG9tZXIgTmFtZQ==\",\"old_field_name\":\"customer_name\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"default_field\":\"1\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"customer_details\",\"field_label\":\"Q3VzdG9tZXIgRGV0YWlscw==\",\"old_field_name\":\"customer_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"official_email\",\"field_label\":\"T2ZmaWNpYWwgRW1haWw=\",\"old_field_name\":\"official_email\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"email\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"phone\",\"field_label\":\"UGhvbmU=\",\"old_field_name\":\"phone\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"55\",\"size\":\"4\",\"data_type\":\"phone\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"fax\",\"field_label\":\"RmF4\",\"old_field_name\":\"fax\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"55\",\"size\":\"4\",\"data_type\":\"phone\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"head_office_address\",\"field_label\":\"SGVhZCBPZmZpY2UgQWRkcmVzcw==\",\"old_field_name\":\"head_office_address\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"0\",\"field_name\":\"customer_status\",\"field_label\":\"Q3VzdG9tZXIgU3RhdHVz\",\"old_field_name\":\"customer_status\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"6\",\"data_type\":\"radio\",\"csvoptions\":\"Active,Inactive\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"0\",\"field_name\":\"customer_type\",\"field_label\":\"Q3VzdG9tZXIgVHlwZQ==\",\"old_field_name\":\"customer_type\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"6\",\"data_type\":\"radio\",\"csvoptions\":\"Lead,New,Existing\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"}]',NULL,NULL,NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('93b76a14-a8be-43b2-8fd0-eda477388006',7,'581c1bb5db15645d76a7e672f882ac71',NULL,'Customer Complaints','','tbl_customer_complaints_0_v0s',0,0,'1402808120',NULL,NULL,NULL,'888fb842-bc2d-41c4-b17e-7026dd7643a5',NULL,NULL,NULL,NULL,0,'[{\"dummy\":\"-1\",\"field_name\":\"customer\",\"field_label\":\"Q3VzdG9tZXI=\",\"old_field_name\":\"customer\",\"linked_to\":\"TblCustomerDetails0V0s\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"12\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"0\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"complaint_details\",\"field_label\":\"Q29tcGxhaW50IERldGFpbHM=\",\"old_field_name\":\"complaint_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"6\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"date_received\",\"field_label\":\"RGF0ZSBSZWNlaXZlZA==\",\"old_field_name\":\"date_received\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"date\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"7\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"\",\"field_name\":\"target_date\",\"field_label\":\"VGFyZ2V0IERhdGU=\",\"old_field_name\":\"target_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"date\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"8\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"date_received\"},{\"dummy\":\"\",\"field_name\":\"closure_date\",\"field_label\":\"Q2xvc3VyZSBEYXRl\",\"old_field_name\":\"closure_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"4\",\"data_type\":\"date\",\"mandatory\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"9\",\"add_disabled\":\"1\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"-1\",\"field_name\":\"assigned_to\",\"field_label\":\"QXNzaWduZWQgVG8=\",\"old_field_name\":\"assigned_to\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"10\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"0\",\"field_name\":\"current_status\",\"field_label\":\"Q3VycmVudCBTdGF0dXM=\",\"old_field_name\":\"current_status\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"6\",\"data_type\":\"radio\",\"csvoptions\":\"Open,Closed\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"11\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"resolution_details\",\"field_label\":\"UmVzb2x1dGlvbiBEZXRhaWxz\",\"old_field_name\":\"resolution_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"12\",\"add_disabled\":\"1\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"customer_number\",\"field_label\":\"Q3VzdG9tZXIgTnVtYmVy\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"mandatory\":\"1\",\"is_unique\":\"1\",\"default_field\":\"1\",\"index_show\":\"1\",\"new\":\"1\",\"sequence\":\"9\",\"who_can_edit\":\"null\"}]',NULL,NULL,NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('395bcbab-a29b-4f92-9dce-e80c5a8ddb96',8,'581c1bb5db15645d76a7e672f882ac71',NULL,'Supplier Details','','tbl_supplier_details_0_v0s',0,0,'1158818613',NULL,NULL,NULL,'e53215e1-ccea-4017-8fee-bd56f4b9975d',NULL,NULL,NULL,NULL,0,'[{\"dummy\":\"\",\"field_name\":\"name\",\"field_label\":\"TmFtZQ==\",\"old_field_name\":\"name\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"default_field\":\"0\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"0\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"supplier_address\",\"field_label\":\"U3VwcGxpZXIgQWRkcmVzcw==\",\"old_field_name\":\"supplier_address\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"1\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"supplier_phone\",\"field_label\":\"U3VwcGxpZXIgUGhvbmU=\",\"old_field_name\":\"supplier_phone\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"55\",\"size\":\"6\",\"data_type\":\"phone\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"2\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"supplier_email\",\"field_label\":\"U3VwcGxpZXIgRW1haWw=\",\"old_field_name\":\"supplier_email\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"email\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"3\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"supplier_since\",\"field_label\":\"U3VwcGxpZXIgU2luY2U=\",\"old_field_name\":\"supplier_since\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"4\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"0\",\"field_name\":\"supplier_company_type\",\"field_label\":\"U3VwcGxpZXIgQ29tcGFueSBUeXBl\",\"old_field_name\":\"supplier_company_type\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"6\",\"data_type\":\"radio\",\"csvoptions\":\"Self-owned, Partnership Firm, LLP, Pvt Ltd., Ltd. \",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"5\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"supplier_number\",\"field_label\":\"U3VwcGxpZXIgTnVtYmVy\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"mandatory\":\"1\",\"is_unique\":\"1\",\"default_field\":\"1\",\"index_show\":\"1\",\"new\":\"1\",\"sequence\":\"7\",\"who_can_edit\":\"null\"}]',NULL,NULL,NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('b5eaa2ea-e624-4028-887c-c55d3dc1cece',9,'581c1bb5db15645d76a7e672f882ac71',NULL,'Device Equipment','','tbl_device_equipment_0_v0s',0,0,'2861404218',NULL,NULL,NULL,'5eea859b-aab2-4c26-8af3-abd16bbd02df',NULL,NULL,NULL,NULL,0,'[{\"dummy\":\"\",\"field_name\":\"number\",\"field_label\":\"bnVtYmVy\",\"old_field_name\":\"name\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"text\",\"default_field\":\"1\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"equipment_name\",\"field_label\":\"RXF1aXBtZW50IE5hbWU=\",\"old_field_name\":\"equipment_name\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"text\",\"default_field\":\"0\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"-1\",\"field_name\":\"location\",\"field_label\":\"TG9jYXRpb24=\",\"old_field_name\":\"location\",\"linked_to\":\"Branches\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"in_service_since\",\"field_label\":\"SW4gU2VydmljZSBTaW5jZQ==\",\"old_field_name\":\"in_service_since\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"\",\"field_name\":\"equipment_details\",\"field_label\":\"RXF1aXBtZW50IERldGFpbHM=\",\"old_field_name\":\"equipment_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"manufacturer_part_number\",\"field_label\":\"TWFudWZhY3R1cmVyIFBhcnQgTnVtYmVy\",\"old_field_name\":\"manufacturer_part_number\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"text\",\"default_field\":\"0\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"-1\",\"field_name\":\"maintenance_schedule\",\"field_label\":\"TWFpbnRlbmFuY2UgU2NoZWR1bGU=\",\"old_field_name\":\"maintenance_schedule\",\"linked_to\":\"Schedules\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"}]',NULL,NULL,NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('e60b9ebd-6a04-4caa-975d-c7882a65a704',10,'581c1bb5db15645d76a7e672f882ac71',NULL,'Calibration','','tbl_calibration_0_v0s',0,0,'185519025',NULL,NULL,NULL,'a9d9fd99-157e-4028-a223-0d72b6d241c8',NULL,NULL,NULL,NULL,0,'[{\"dummy\":\"\",\"field_name\":\"number\",\"field_label\":\"bnVtYmVy\",\"old_field_name\":\"name\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"text\",\"default_field\":\"1\",\"mandatory\":\"1\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"equipement\",\"field_label\":\"RXF1aXBlbWVudA==\",\"old_field_name\":\"equipement\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"text\",\"default_field\":\"0\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"tool_details\",\"field_label\":\"VG9vbCBEZXRhaWxz\",\"old_field_name\":\"tool_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"text\",\"default_field\":\"0\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"-1\",\"field_name\":\"tool_location\",\"field_label\":\"VG9vbCBMb2NhdGlvbg==\",\"old_field_name\":\"tool_location\",\"linked_to\":\"Branches\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"-1\",\"field_name\":\"calibration_frequency\",\"field_label\":\"Q2FsaWJyYXRpb24gRnJlcXVlbmN5\",\"old_field_name\":\"calibration_frequency\",\"linked_to\":\"Schedules\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"dummy\":\"\",\"field_name\":\"previous_calibration_date\",\"field_label\":\"UHJldmlvdXMgQ2FsaWJyYXRpb24gRGF0ZQ==\",\"old_field_name\":\"previous_calibration_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"\",\"field_name\":\"next_calibration_date\",\"field_label\":\"TmV4dCBDYWxpYnJhdGlvbiBEYXRl\",\"old_field_name\":\"next_calibration_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"default_date_number\":\"0\",\"default_date_type\":\"-1\",\"default_date_from\":\"-1\"},{\"dummy\":\"-1\",\"field_name\":\"calibration_performed_by\",\"field_label\":\"Q2FsaWJyYXRpb24gUGVyZm9ybWVkIEJ5\",\"old_field_name\":\"calibration_performed_by\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"1\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"1\",\"sequence\":\"\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\"},{\"field_name\":\"comments8\",\"show_comments\":\"WW91IGNhbiBhZGQgYWN0dWFsIGNhbGlicmF0aW9uIHJlYWRpbmdzIGluc2lkZSB0aGUgY2FsaWJyYXRpb24gZG9jdW1lbnQu\",\"size\":\"12\",\"display_type\":\"7\",\"field_type\":\"0\",\"data_type\":\"comments\",\"mandatory\":\"0\",\"index_show\":\"0\",\"new\":\"1\",\"sequence\":\"8\",\"linked_to\":\"-1\",\"dummy\":\"0\",\"drop\":\"0\",\"old_field_name\":\"0\",\"add_disabled\":\"0\",\"who_can_edit\":\"\\\"\\\"\",\"field_label\":\"\"}]',NULL,NULL,NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('2fcdbccf-b34b-467e-957e-da9a1eb934e2',11,'581c1bb5db15645d76a7e672f882ac71',NULL,'MRM Child 1','','chd_mrm0_child_1_v1s',1,0,NULL,NULL,NULL,NULL,'ea17ca9b-c5ec-4f0d-ad7c-14f534b5f32d',NULL,NULL,'','d9385db9-54d3-4569-b625-7507181d00d5',0,'[{\"dummy\":\"\",\"field_name\":\"audit_number\",\"field_label\":\"QXVkaXQgTnVtYmVy\",\"old_field_name\":\"audit_number\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"mandatory\":\"1\",\"default_field\":\"1\",\"is_unique\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"show_last_value\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"agenda_details\",\"field_label\":\"QWdlbmRhIERldGFpbHM=\",\"old_field_name\":\"agenda_details\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"0\",\"length\":\"255\",\"size\":\"12\",\"data_type\":\"text\",\"mandatory\":\"0\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"-1\",\"field_name\":\"assigned_to\",\"field_label\":\"QXNzaWduZWQgVG8=\",\"old_field_name\":\"assigned_to\",\"linked_to\":\"Employees\",\"display_type\":\"3\",\"field_type\":\"0\",\"length\":\"36\",\"size\":\"6\",\"data_type\":\"dropdown-s\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_signature\":\"0\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"\",\"field_name\":\"target_date\",\"field_label\":\"VGFyZ2V0IERhdGU=\",\"old_field_name\":\"target_date\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"5\",\"length\":\"255\",\"size\":\"6\",\"data_type\":\"date\",\"mandatory\":\"1\",\"default_field\":\"0\",\"is_unique\":\"0\",\"index_show\":\"1\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"0\",\"edit_disabled\":\"0\",\"who_can_edit\":\"[\\\"prepared_by\\\"]\",\"session_value\":\"\",\"default_date_number\":\"1\",\"default_date_type\":\"1\",\"default_date_from\":\"Today\"},{\"dummy\":\"\",\"field_name\":\"closure_comments\",\"field_label\":\"Q2xvc3VyZSBDb21tZW50cw==\",\"old_field_name\":\"closure_comments\",\"linked_to\":\"-1\",\"display_type\":\"0\",\"field_type\":\"1\",\"length\":\"0\",\"size\":\"12\",\"data_type\":\"textarea\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"1\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"},{\"dummy\":\"0\",\"field_name\":\"current_status\",\"field_label\":\"Q3VycmVudCBTdGF0dXM=\",\"old_field_name\":\"current_status\",\"linked_to\":\"-1\",\"display_type\":\"1\",\"field_type\":\"2\",\"length\":\"1\",\"size\":\"12\",\"data_type\":\"radio\",\"csvoptions\":\"Open,Closed\",\"mandatory\":\"0\",\"index_show\":\"0\",\"drop\":\"0\",\"new\":\"0\",\"sequence\":\"\",\"add_disabled\":\"1\",\"edit_disabled\":\"0\",\"who_can_edit\":\"\",\"session_value\":\"\"}]',NULL,'',NULL,2,NULL,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]','[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,1,0,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,NULL,'2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id');
/*!40000 ALTER TABLE `custom_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_triggers`
--

DROP TABLE IF EXISTS `custom_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_triggers` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `custom_table_id` varchar(36) NOT NULL,
  `action` int(1) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `field_name` varchar(250) DEFAULT NULL,
  `changed_field_value` varchar(250) DEFAULT NULL,
  `notify_user` varchar(36) DEFAULT NULL,
  `notify_users` text DEFAULT NULL,
  `hod_departments` text DEFAULT NULL,
  `notify_admins` tinyint(1) DEFAULT 0,
  `notify_hods` tinyint(1) DEFAULT 0,
  `notify_departments` tinyint(1) DEFAULT NULL,
  `notify_branches` tinyint(1) DEFAULT NULL,
  `notify_designations` tinyint(1) DEFAULT NULL,
  `recipents` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_triggers`
--

LOCK TABLES `custom_triggers` WRITE;
/*!40000 ALTER TABLE `custom_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_triggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gstin` varchar(45) NOT NULL,
  `notes` text DEFAULT NULL,
  `company_id` varchar(36) NOT NULL,
  `currency` varchar(5) NOT NULL DEFAULT 'USD',
  `data_cost_per_unit` float(11,2) NOT NULL,
  `db_cost_per_unit` float(11,2) NOT NULL,
  `discount` float(11,2) NOT NULL,
  `credit_days` int(2) NOT NULL DEFAULT 7,
  `retention_period` int(2) NOT NULL DEFAULT 45,
  `created_at` datetime NOT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `clauses` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES ('f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,'Quality Management',NULL,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','0',NULL,NULL,'2026-07-06 16:11:37','0');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `designations` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `division_id` varchar(36) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
INSERT INTO `designations` VALUES ('cf3911c1-80d0-4272-85c2-8c574627f0a2',1,'QA Manager',NULL,0,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','0',NULL,NULL,'2026-07-06 16:11:37','0','0');
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_change_requests`
--

DROP TABLE IF EXISTS `document_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_change_requests` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `division_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_change_requests`
--

LOCK TABLES `document_change_requests` WRITE;
/*!40000 ALTER TABLE `document_change_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_change_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_downloads`
--

DROP TABLE IF EXISTS `document_downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_downloads` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `qc_document_id` varchar(36) DEFAULT NULL,
  `custom_table_id` varchar(36) DEFAULT NULL,
  `record_id` varchar(36) DEFAULT NULL,
  `file_id` varchar(36) DEFAULT NULL,
  `download_by` varchar(36) NOT NULL,
  `issue` int(11) DEFAULT 0,
  `signature` text DEFAULT NULL,
  `digital_signature` text DEFAULT NULL,
  `add_document` tinyint(1) NOT NULL COMMENT '0=yes,1=no',
  `add_cover_page` tinyint(1) DEFAULT NULL COMMENT '0=yes,1=No',
  `add_parent_records` tinyint(1) DEFAULT NULL COMMENT '0=yes,1=No',
  `add_child_records` tinyint(1) DEFAULT NULL COMMENT '0=yes,1=No',
  `add_linked_form_records` tinyint(1) DEFAULT NULL COMMENT '0=yes,1=No',
  `created_by` varchar(36) NOT NULL,
  `download` tinyint(1) DEFAULT NULL,
  `downoad_time` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_downloads`
--

LOCK TABLES `document_downloads` WRITE;
/*!40000 ALTER TABLE `document_downloads` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_downloads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_leaves`
--

DROP TABLE IF EXISTS `employee_leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_leaves` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(36) DEFAULT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `who_approved` varchar(36) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `other` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_leaves`
--

LOCK TABLES `employee_leaves` WRITE;
/*!40000 ALTER TABLE `employee_leaves` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `employee_number` char(20) NOT NULL,
  `identification_number` char(20) DEFAULT NULL,
  `branch_id` varchar(36) NOT NULL,
  `department_id` varchar(36) DEFAULT NULL,
  `designation_id` varchar(36) DEFAULT NULL,
  `is_hod` int(1) NOT NULL DEFAULT 0,
  `qualification` varchar(255) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `pancard_number` char(15) DEFAULT 'NULL',
  `personal_telephone` varchar(120) DEFAULT NULL,
  `office_telephone` varchar(120) DEFAULT 'NULL',
  `mobile` varchar(120) DEFAULT 'NULL',
  `personal_email` varchar(250) DEFAULT 'NULL',
  `office_email` varchar(255) NOT NULL,
  `residence_address` text DEFAULT NULL,
  `permenant_address` text DEFAULT NULL,
  `maritial_status` int(11) DEFAULT NULL,
  `driving_license` char(40) DEFAULT 'NULL',
  `employment_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Resigned, 1=Active',
  `is_approver` tinyint(1) DEFAULT 0,
  `signature` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime DEFAULT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES ('54402c6e-7e5d-4572-b34d-2afa43cf2799',1,'Admin',NULL,'FLI001',NULL,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','cf3911c1-80d0-4272-85c2-8c574627f0a2',0,NULL,'2026-07-06',NULL,'NULL','0000000000','0000000000','0000000000','admin@flinkiso.local','admin@flinkiso.local',NULL,NULL,NULL,'NULL',1,0,NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','0',NULL,NULL,'2026-07-06 16:11:37','5297b2e7-959c-4892-b073-2d8f0a000005','11111111-1111-1111-1111-111111111111');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `data_received` text DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `file_type` varchar(5) NOT NULL,
  `file_status` int(1) DEFAULT 0 COMMENT '0=copied,1=saved',
  `last_saved` datetime DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `pre_file_id` varchar(50) DEFAULT NULL,
  `file_key` varchar(50) NOT NULL,
  `pre_file_key` varchar(50) DEFAULT NULL,
  `version_keys` text DEFAULT NULL,
  `versions` text DEFAULT NULL,
  `user_id` varchar(36) NOT NULL,
  `qc_document_id` varchar(36) DEFAULT NULL,
  `process_id` varchar(36) DEFAULT NULL,
  `custom_table_id` varchar(36) NOT NULL,
  `record_id` varchar(36) NOT NULL,
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graph_panels`
--

DROP TABLE IF EXISTS `graph_panels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graph_panels` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `custom_table_id` varchar(36) NOT NULL,
  `field_name` varchar(250) NOT NULL,
  `linked_to` varchar(250) DEFAULT NULL,
  `date_condition` int(1) NOT NULL,
  `graph_type` int(1) NOT NULL DEFAULT 0,
  `data_type` int(11) DEFAULT 0 COMMENT '0=count,1=sum,2=avg',
  `value_field` varchar(255) DEFAULT NULL,
  `color` varchar(8) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `size` int(11) DEFAULT 3,
  `admin_only` int(1) DEFAULT NULL,
  `branches` text DEFAULT NULL,
  `departments` text DEFAULT NULL,
  `designations` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graph_panels`
--

LOCK TABLES `graph_panels` WRITE;
/*!40000 ALTER TABLE `graph_panels` DISABLE KEYS */;
/*!40000 ALTER TABLE `graph_panels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `histories`
--

DROP TABLE IF EXISTS `histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `histories` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(225) DEFAULT NULL,
  `controller_name` varchar(250) DEFAULT NULL,
  `action` varchar(225) DEFAULT NULL,
  `record_id` varchar(36) DEFAULT NULL,
  `get_values` longtext DEFAULT NULL,
  `pre_post_values` longtext DEFAULT NULL,
  `post_values` longtext DEFAULT NULL,
  `user_session_id` varchar(36) DEFAULT NULL,
  `branch_id` varchar(36) DEFAULT NULL,
  `department_id` varchar(36) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `created` datetime DEFAULT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime DEFAULT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `division_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `histories`
--

LOCK TABLES `histories` WRITE;
/*!40000 ALTER TABLE `histories` DISABLE KEYS */;
INSERT INTO `histories` VALUES ('9b62a171-4c4c-448b-ab01-123f9beb9972',1,'User','users','dashboard',NULL,'{\"plugin\":null,\"controller\":\"users\",\"action\":\"dashboard\",\"named\":[],\"pass\":[]}',NULL,'[[]]','fa8530fa-91b1-4841-a4c1-3c0790adc711','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:17:42','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:17:42','0','0','11111111-1111-1111-1111-111111111111'),('213f6fb4-e34e-41f4-8165-1f0cf3dc21a2',2,'CustomTable','custom_tables','last_updated_record','tbl_audit_schedule_0_v0s','{\"plugin\":null,\"controller\":\"custom_tables\",\"action\":\"last_updated_record\",\"named\":[],\"pass\":[\"tbl_audit_schedule_0_v0s\",\"2468b0e4-b6dc-45d8-b117-002868c22d79\",\"2\",\"52487033-b1a8-436f-b0a9-53a7q6c3268c\"],\"url\":[],\"autoRender\":0,\"return\":1,\"bare\":1,\"requested\":1}',NULL,'[[]]','fa8530fa-91b1-4841-a4c1-3c0790adc711','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:17:42','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:17:42','0','0','11111111-1111-1111-1111-111111111111'),('9bda99eb-f881-4da3-9d13-097c2ba251a1',3,'CustomTable','custom_tables','last_updated_record','tbl_calibration_0_v0s','{\"plugin\":null,\"controller\":\"custom_tables\",\"action\":\"last_updated_record\",\"named\":[],\"pass\":[\"tbl_calibration_0_v0s\",\"2468b0e4-b6dc-45d8-b117-002868c22d79\",\"1\",\"52487027-260c-4196-8062-543bn6c3268c\"],\"url\":[],\"autoRender\":0,\"return\":1,\"bare\":1,\"requested\":1}',NULL,'[[]]','fa8530fa-91b1-4841-a4c1-3c0790adc711','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:17:42','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:17:42','0','0','11111111-1111-1111-1111-111111111111'),('47ff5647-4a2e-4ed4-b4b2-1f8a710157b3',4,'CustomTable','custom_tables','last_updated_record','tbl_mrm_0_v0s','{\"plugin\":null,\"controller\":\"custom_tables\",\"action\":\"last_updated_record\",\"named\":[],\"pass\":[\"tbl_mrm_0_v0s\",\"2468b0e4-b6dc-45d8-b117-002868c22d79\",\"2\",\"52487027-260c-4196-8062-543bn6c3268c\"],\"url\":[],\"autoRender\":0,\"return\":1,\"bare\":1,\"requested\":1}',NULL,'[[]]','fa8530fa-91b1-4841-a4c1-3c0790adc711','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:17:42','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:17:42','0','0','11111111-1111-1111-1111-111111111111'),('414e8ba6-e0b9-4039-8201-1149fc560e8d',5,'User','users','dashboard',NULL,'{\"plugin\":null,\"controller\":\"users\",\"action\":\"dashboard\",\"named\":[],\"pass\":[]}',NULL,'[[]]','9aa4787b-8762-40d8-bbe1-2e58dbf3b1b5','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:18:47','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:18:47','0','0','11111111-1111-1111-1111-111111111111'),('edf9a996-23c5-4752-8838-33cfdcbd240b',6,'CustomTable','custom_tables','last_updated_record','tbl_audit_schedule_0_v0s','{\"plugin\":null,\"controller\":\"custom_tables\",\"action\":\"last_updated_record\",\"named\":[],\"pass\":[\"tbl_audit_schedule_0_v0s\",\"2468b0e4-b6dc-45d8-b117-002868c22d79\",\"2\",\"52487033-b1a8-436f-b0a9-53a7q6c3268c\"],\"url\":[],\"autoRender\":0,\"return\":1,\"bare\":1,\"requested\":1}',NULL,'[[]]','9aa4787b-8762-40d8-bbe1-2e58dbf3b1b5','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:18:47','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:18:47','0','0','11111111-1111-1111-1111-111111111111'),('6b3c1a9d-dbe5-42cb-947e-538a38d05e5f',7,'CustomTable','custom_tables','last_updated_record','tbl_calibration_0_v0s','{\"plugin\":null,\"controller\":\"custom_tables\",\"action\":\"last_updated_record\",\"named\":[],\"pass\":[\"tbl_calibration_0_v0s\",\"2468b0e4-b6dc-45d8-b117-002868c22d79\",\"1\",\"52487027-260c-4196-8062-543bn6c3268c\"],\"url\":[],\"autoRender\":0,\"return\":1,\"bare\":1,\"requested\":1}',NULL,'[[]]','9aa4787b-8762-40d8-bbe1-2e58dbf3b1b5','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:18:47','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:18:47','0','0','11111111-1111-1111-1111-111111111111'),('3cbd3027-7d0d-49c3-a025-c40b94468a8d',8,'CustomTable','custom_tables','last_updated_record','tbl_mrm_0_v0s','{\"plugin\":null,\"controller\":\"custom_tables\",\"action\":\"last_updated_record\",\"named\":[],\"pass\":[\"tbl_mrm_0_v0s\",\"2468b0e4-b6dc-45d8-b117-002868c22d79\",\"2\",\"52487027-260c-4196-8062-543bn6c3268c\"],\"url\":[],\"autoRender\":0,\"return\":1,\"bare\":1,\"requested\":1}',NULL,'[[]]','9aa4787b-8762-40d8-bbe1-2e58dbf3b1b5','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:18:47','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:18:47','0','0','11111111-1111-1111-1111-111111111111');
/*!40000 ALTER TABLE `histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `razorpay_id` varchar(36) DEFAULT NULL,
  `entity` varchar(36) DEFAULT 'invoice',
  `type` varchar(36) DEFAULT 'invoice',
  `draft` int(1) DEFAULT NULL,
  `invoice_number` varchar(120) NOT NULL,
  `invoice_date` date NOT NULL,
  `customer_id` varchar(36) NOT NULL,
  `order_id` varchar(36) DEFAULT NULL,
  `payment_id` varchar(36) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'draft',
  `expire_by` date NOT NULL,
  `issued_at` date NOT NULL,
  `paid_at` date DEFAULT NULL,
  `cancelled_at` date DEFAULT NULL,
  `expired_at` date DEFAULT NULL,
  `email_status` varchar(10) DEFAULT NULL,
  `partial_payment` tinyint(1) NOT NULL DEFAULT 0,
  `amount` float(11,2) NOT NULL,
  `amount_paid` int(11) DEFAULT NULL,
  `amount_due` int(11) DEFAULT NULL,
  `currency` varchar(5) NOT NULL,
  `description` varchar(255) NOT NULL,
  `item_details` text NOT NULL,
  `notes` text DEFAULT NULL,
  `short_url` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `comment` varchar(250) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_settings`
--

DROP TABLE IF EXISTS `password_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_settings` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `password_max_len` int(2) DEFAULT NULL,
  `password_min_len` int(2) DEFAULT NULL,
  `display_policy` tinyint(1) DEFAULT NULL,
  `concurrent_login` int(1) DEFAULT NULL,
  `password_change_remind` int(2) DEFAULT NULL,
  `password_uppercase_length` int(2) DEFAULT NULL,
  `password_uppercase_start` int(2) DEFAULT NULL,
  `password_special_character` int(1) DEFAULT NULL,
  `password_same_username` int(1) DEFAULT NULL,
  `password_repeat` int(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `record_status` tinyint(1) DEFAULT NULL,
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0,
  `branchid` varchar(36) NOT NULL,
  `departmentid` varchar(36) NOT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `modified` datetime NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `system_table_id` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_settings`
--

LOCK TABLES `password_settings` WRITE;
/*!40000 ALTER TABLE `password_settings` DISABLE KEYS */;
INSERT INTO `password_settings` VALUES ('c958dee2-1cd9-4601-8c6e-4f6ce471e608',1,10,3,1,1,3,1,1,1,1,3,1,NULL,NULL,0,'43bcb91e-d10b-4d9c-9d62-edc6e97b6261','da75b706-16d4-41f2-92f5-6f1a0b165057','e6c97faa-f602-4f37-ae2d-7c95c11e1817','2025-03-11 08:17:08','e6c97faa-f602-4f37-ae2d-7c95c11e1817','2025-04-16 00:45:58',NULL,NULL,NULL,'update_comany_id');
/*!40000 ALTER TABLE `password_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdf_templates`
--

DROP TABLE IF EXISTS `pdf_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pdf_templates` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `custom_table_id` varchar(36) NOT NULL,
  `template_type` int(11) DEFAULT NULL,
  `header` int(11) DEFAULT NULL,
  `outline` int(11) DEFAULT NULL,
  `dpi` int(11) DEFAULT NULL,
  `outline_depth` int(11) DEFAULT NULL,
  `header_spacing` int(11) DEFAULT NULL,
  `footer_left` varchar(55) DEFAULT NULL,
  `footer_center` varchar(55) DEFAULT NULL,
  `footer_right` varchar(55) DEFAULT NULL,
  `footer_font_size` int(11) DEFAULT NULL,
  `margin_bottom` int(11) DEFAULT NULL,
  `margin_left` int(11) DEFAULT NULL,
  `margin_right` int(11) DEFAULT NULL,
  `margin_top` int(11) DEFAULT NULL,
  `html_cleanup` int(11) DEFAULT NULL,
  `template` text NOT NULL,
  `child_table_fields` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdf_templates`
--

LOCK TABLES `pdf_templates` WRITE;
/*!40000 ALTER TABLE `pdf_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdf_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processes`
--

DROP TABLE IF EXISTS `processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `processes` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `file_type` varchar(5) DEFAULT NULL,
  `version_keys` text DEFAULT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT '4768007e-8971-48b0-b6f8-4a0fb50d2425',
  `custom_table_id` varchar(36) NOT NULL DEFAULT 'd5e155ee-2de3-414f-8796-b90a45579e72',
  `process_definition` text DEFAULT NULL,
  `process_objective_and_metrics` text DEFAULT NULL,
  `process_owners` text DEFAULT NULL,
  `applicable_to_branches` text DEFAULT NULL,
  `additional_responsibilities` text DEFAULT NULL,
  `input_processes` text DEFAULT NULL,
  `output_processes` text DEFAULT NULL,
  `process_output` text DEFAULT NULL,
  `risks_and_opportunities` text DEFAULT NULL,
  `standards` text DEFAULT NULL,
  `clauses` text DEFAULT NULL,
  `schedule_id` varchar(36) DEFAULT NULL,
  `data_types` int(1) DEFAULT NULL COMMENT '0=''Document'',1=''Data'',2=''Both''',
  `add_records` int(1) DEFAULT 0,
  `process_status` int(1) DEFAULT NULL COMMENT '''0'' => ''Draft'',''1'' = ''Published/Issued'',''2'' = ''Approved'',''3'' = ''Under Revision'',''4'' = ''Archived'',''5'' = ''Awaiting Issue''',
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processes`
--

LOCK TABLES `processes` WRITE;
/*!40000 ALTER TABLE `processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_document_categories`
--

DROP TABLE IF EXISTS `qc_document_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qc_document_categories` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(10) DEFAULT NULL,
  `standard_id` varchar(36) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT '-1',
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_document_categories`
--

LOCK TABLES `qc_document_categories` WRITE;
/*!40000 ALTER TABLE `qc_document_categories` DISABLE KEYS */;
INSERT INTO `qc_document_categories` VALUES ('584dba0b-26f4-40e3-840d-68a8c20b8995',1,'Level 1 - The Quality Manual',NULL,'58511238-fba8-4db9-aad0-833fc20b8995',NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id'),('584dba22-189c-40fa-8ed4-0259c20b8995',2,'Level 2: Quality Manual - approach and responsibility',NULL,'58511238-fba8-4db9-aad0-833fc20b8995',NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id'),('584dba30-95e4-4bd2-bc3e-6897c20b8995',3,'Level 3: Procedures - methods (Who, What, Where and When)',NULL,'58511238-fba8-4db9-aad0-833fc20b8995',NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id'),('584dbb51-3640-4357-b0a6-2e58c20b8995',4,'Level 4: Work Instructions - description of processes (How)',NULL,'58511238-fba8-4db9-aad0-833fc20b8995',NULL,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id'),('584dbb5d-f880-44a3-8b0d-2e7ec20b8995',5,'Level 5: Forms, Data and Records - evidence of conformance','F','58511238-fba8-4db9-aad0-833fc20b8995','-1',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37',NULL,'update_comany_id');
/*!40000 ALTER TABLE `qc_document_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_documents`
--

DROP TABLE IF EXISTS `qc_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qc_documents` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(250) NOT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `versions` text DEFAULT NULL,
  `version_keys` text DEFAULT NULL,
  `file_status` int(1) DEFAULT 0 COMMENT '0=copied,1=saved',
  `last_saved` datetime DEFAULT NULL,
  `update_custom_table_document` int(1) DEFAULT 0 COMMENT '0=no,1=Yes',
  `file_type` varchar(5) DEFAULT NULL,
  `schedule_id` varchar(36) DEFAULT NULL,
  `data_update_type` int(1) DEFAULT 0,
  `data_type` int(1) NOT NULL DEFAULT 2,
  `data_file_type` int(1) DEFAULT 0,
  `add_records` int(1) DEFAULT 0,
  `qc_document_category_id` varchar(36) DEFAULT NULL,
  `clause_id` varchar(36) DEFAULT NULL,
  `standard_id` varchar(36) DEFAULT NULL,
  `additional_clauses` varchar(255) DEFAULT NULL,
  `document_number` varchar(100) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `issue_number` varchar(2) DEFAULT '0',
  `date_of_next_issue` date DEFAULT NULL,
  `date_of_issue` date NOT NULL,
  `effective_from_date` date DEFAULT NULL,
  `revision_number` int(2) DEFAULT NULL,
  `date_created` date DEFAULT NULL,
  `update_version` int(1) DEFAULT 0,
  `date_of_review` date DEFAULT NULL,
  `revision_date` date DEFAULT NULL,
  `document_type` int(1) NOT NULL,
  `it_categories` int(1) DEFAULT NULL,
  `document_status` int(1) NOT NULL DEFAULT 0 COMMENT '0=draft 1=published 2=Under Revision 3=Archived',
  `issued_by` varchar(36) DEFAULT NULL,
  `issuing_authority_id` varchar(36) DEFAULT NULL,
  `published_by` varchar(36) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT 0,
  `allow_download` tinyint(4) DEFAULT NULL,
  `allow_print` tinyint(4) DEFAULT NULL,
  `change_history` text DEFAULT NULL,
  `cr_status` int(1) DEFAULT NULL,
  `mark_for_cr_update` int(1) DEFAULT 0,
  `temp_date_of_issue` date DEFAULT NULL,
  `temp_effective_from_date` date DEFAULT NULL,
  `cr_id` varchar(36) DEFAULT NULL,
  `old_cr_id` varchar(36) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `parent_document_id` varchar(36) DEFAULT NULL,
  `linked_documents` text DEFAULT NULL,
  `user_id` text DEFAULT NULL,
  `cover_page` tinyint(1) DEFAULT 0,
  `page_orientation` tinyint(1) DEFAULT 0,
  `pdf_footer_id` varchar(36) DEFAULT NULL,
  `branches` text DEFAULT NULL,
  `departments` text DEFAULT NULL,
  `designations` text DEFAULT NULL,
  `and_or_condition` tinyint(1) DEFAULT 0 COMMENT '0=And;1=OR',
  `editors` text DEFAULT NULL,
  `system_table_id` varchar(36) DEFAULT NULL,
  `user_session_id` varchar(36) DEFAULT NULL,
  `approval_step_id` varchar(36) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) DEFAULT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `reviewed_by` varchar(36) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_documents`
--

LOCK TABLES `qc_documents` WRITE;
/*!40000 ALTER TABLE `qc_documents` DISABLE KEYS */;
INSERT INTO `qc_documents` VALUES ('9b8144a1-6393-4955-bf86-b508fac030ba',1,'QMS Manual','qms_manual','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',0,2,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'001',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('c29a4cae-ee08-4dc8-9e30-ba6bce0005b4',2,'Audit Schedule','audit_schedule','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','52487033-b1a8-436f-b0a9-53a7q6c3268c',2,0,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995','null','002',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,0,0,'',NULL,0,NULL,NULL,'',NULL,'','-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,'',NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('cdf45c35-4a25-4cc9-bec7-4692f40d27af',3,'Audit Checklist','audit_checklist','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',2,0,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995','null','003',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,0,0,'',NULL,0,NULL,NULL,'',NULL,'','c29a4cae-ee08-4dc8-9e30-ba6bce0005b4',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,'',NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('5abc3b5b-d7e5-4252-8fe2-d21200c26556',4,'Audit Findings','audit_findings','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',2,0,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995','null','004',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,0,0,'',NULL,0,NULL,NULL,'',NULL,'','c29a4cae-ee08-4dc8-9e30-ba6bce0005b4',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,'',NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('ea17ca9b-c5ec-4f0d-ad7c-14f534b5f32d',5,'MRM','mrm','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','52487027-260c-4196-8062-543bn6c3268c',2,0,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'005',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('06d28e70-8ed6-424d-8a8c-deb73f5f5510',6,'Customer Details','customer_details','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',0,1,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'006',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('888fb842-bc2d-41c4-b17e-7026dd7643a5',7,'Customer Complaints','customer_complaints','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',0,1,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'007',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('e53215e1-ccea-4017-8fee-bd56f4b9975d',8,'Supplier Details','supplier_details','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',0,1,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'008',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('5eea859b-aab2-4c26-8af3-abd16bbd02df',9,'Device/ Equipment','device_equipment','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','56d1564b-0acc-48f6-9beb-03a7db1e6cf9',0,1,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'009',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id'),('a9d9fd99-157e-4028-a223-0d72b6d241c8',10,'Calibration','calibration','4118439914',NULL,NULL,NULL,0,NULL,0,'docx','52487027-260c-4196-8062-543bn6c3268c',1,0,0,1,'584dbb5d-f880-44a3-8b0d-2e7ec20b8995','33457b87-110e-4bba-bf46-24f34952d44b','58511238-fba8-4db9-aad0-833fc20b8995',NULL,'010',NULL,'0',NULL,'2026-01-11','2026-01-11',0,'2026-01-11',0,NULL,NULL,6,0,1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','017838bb-2f06-4b2c-84c0-f171c6b3a756',NULL,0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'-1',NULL,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',0,0,NULL,'[\"fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc\"]','[\"f8c874f6-bf8f-4599-aa2d-0b6b0791d96c\"]','[\"cf3911c1-80d0-4272-85c2-8c574627f0a2\"]',0,'[\"2468b0e4-b6dc-45d8-b117-002868c22d79\"]',NULL,NULL,NULL,1,0,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817','54402c6e-7e5d-4572-b34d-2afa43cf2799','54402c6e-7e5d-4572-b34d-2afa43cf2799',NULL,NULL,NULL,'2026-07-06 16:11:37','update_comany_id');
/*!40000 ALTER TABLE `qc_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_locks`
--

DROP TABLE IF EXISTS `record_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_locks` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` varchar(36) NOT NULL,
  `lock_table_id` varchar(255) NOT NULL,
  `table_field` varchar(250) NOT NULL,
  `condition` varchar(10) NOT NULL,
  `csvoption` int(1) DEFAULT 0,
  `action` varchar(10) NOT NULL,
  `message` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_locks`
--

LOCK TABLES `record_locks` WRITE;
/*!40000 ALTER TABLE `record_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `record_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `records`
--

DROP TABLE IF EXISTS `records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `records` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Record Title',
  `qc_document_id` varchar(36) NOT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `file_type` varchar(5) DEFAULT NULL,
  `schedule_id` varchar(36) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `records`
--

LOCK TABLES `records` WRITE;
/*!40000 ALTER TABLE `records` DISABLE KEYS */;
/*!40000 ALTER TABLE `records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedules` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
INSERT INTO `schedules` VALUES ('52487014-1448-45ae-82c3-4f1fc6c3268c',1,'Daily',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','54e5d056-199b-11e3-9f46-c709d410d2ec',NULL,NULL,'2026-07-06 16:11:37','522e4411-7e44-4c41-9c1a-84a2c6c3268c'),('5248701d-1390-4782-9990-4f1fc6c3268c',2,'Weekly',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','54e5d056-199b-11e3-9f46-c709d410d2ec',NULL,NULL,'2026-07-06 16:11:37','522e4411-7e44-4c41-9c1a-84a2c6c3268c'),('52487027-260c-4196-8062-543bn6c3268c',4,'Monthly',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','530c2de0-b334-4661-a55c-383db6329416',NULL,NULL,'2026-07-06 16:11:37','5297b2e7-d99c-464b-952b-2d8f0a000005'),('52487033-b1a8-436f-b0a9-53a7q6c3268c',5,'Quarterly',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','54e5d056-199b-11e3-9f46-c709d410d2ec',NULL,NULL,'2026-07-06 16:11:37','522e4411-7e44-4c41-9c1a-84a2c6c3268c'),('530df9f4-fff8-454e-aa24-71f5b6329416',7,'Yearly',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','530c2de0-b334-4661-a55c-383db6329416',NULL,NULL,'2026-07-06 16:11:37','5297b2e7-d99c-464b-952b-2d8f0a000005'),('56d15631-8f34-40bb-a577-03a2db1e6cf9',8,'Half-Yearly',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9','56044715-6bb8-49bd-85f2-03e1db1e6cf9','56044715-6bb8-49bd-85f2-03e1db1e6cf9','2026-07-06 16:11:37','5297b2e7-d99c-464b-952b-2d8f0a000005'),('56d1564b-0acc-48f6-9beb-03a7db1e6cf9',9,'None',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9','56044715-6bb8-49bd-85f2-03e1db1e6cf9','56044715-6bb8-49bd-85f2-03e1db1e6cf9','2026-07-06 16:11:37','5297b2e7-d99c-464b-952b-2d8f0a000005');
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `standards`
--

DROP TABLE IF EXISTS `standards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `standards` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `short_name` varchar(10) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `division_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `standards`
--

LOCK TABLES `standards` WRITE;
/*!40000 ALTER TABLE `standards` DISABLE KEYS */;
INSERT INTO `standards` VALUES ('58511238-fba8-4db9-aad0-833fc20b8995',1,'2015',NULL,'ISO 2008-2015',1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','56044715-819c-4246-8ec3-03e1db1e6cf9',NULL,NULL,'2026-07-06 16:11:37',NULL,'0','update_comany_id');
/*!40000 ALTER TABLE `standards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_tables`
--

DROP TABLE IF EXISTS `system_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_tables` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `system_name` varchar(250) NOT NULL,
  `iso_section` tinytext DEFAULT NULL,
  `evidence_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=no 1=yes',
  `approvals_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=no 1=yes',
  `reports` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `division_id` varchar(36) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_name` (`system_name`),
  UNIQUE KEY `name` (`name`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_tables`
--

LOCK TABLES `system_tables` WRITE;
/*!40000 ALTER TABLE `system_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_audit_catetory_v0s`
--

DROP TABLE IF EXISTS `tbl_audit_catetory_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_audit_catetory_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `custom_table_id` varchar(36) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_audit_catetory_v0s`
--

LOCK TABLES `tbl_audit_catetory_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_audit_catetory_v0s` DISABLE KEYS */;
INSERT INTO `tbl_audit_catetory_v0s` VALUES ('1ae3e1dc-db60-4fe0-85c4-674bf42c7e8a',1,'General Audit',NULL,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,'017838bb-2f06-4b2c-84c0-f171c6b3a756','2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id'),('f288a4b6-4fff-4b69-a682-8aa2074afcb5',2,'Process Audit',NULL,1,0,NULL,'2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','e6c97faa-f602-4f37-ae2d-7c95c11e1817',NULL,'017838bb-2f06-4b2c-84c0-f171c6b3a756','2026-07-06 16:11:37',0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','update_comany_id');
/*!40000 ALTER TABLE `tbl_audit_catetory_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_audit_checklist_0_v0s`
--

DROP TABLE IF EXISTS `tbl_audit_checklist_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_audit_checklist_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `comments` text DEFAULT NULL,
  `added_by` varchar(36) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `checklist_title` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT 'cdf45c35-4a25-4cc9-bec7-4692f40d27af',
  `custom_table_id` varchar(36) NOT NULL DEFAULT 'b2636904-8dff-4356-a06a-4887341c8def',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_audit_checklist_0_v0s`
--

LOCK TABLES `tbl_audit_checklist_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_audit_checklist_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_audit_checklist_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_audit_findings_0_v0s`
--

DROP TABLE IF EXISTS `tbl_audit_findings_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_audit_findings_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `response_from_auditee` text DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `current_status` int(1) DEFAULT NULL,
  `finding_type` int(1) DEFAULT NULL,
  `auditee` varchar(36) DEFAULT NULL,
  `auditor` varchar(36) DEFAULT NULL,
  `audit_end_date` date DEFAULT NULL,
  `audit_start_date` datetime DEFAULT NULL,
  `finding_number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT '5abc3b5b-d7e5-4252-8fe2-d21200c26556',
  `custom_table_id` varchar(36) NOT NULL DEFAULT '83c439cf-e6ab-42de-b1c8-a941a4ba18f7',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_audit_findings_0_v0s`
--

LOCK TABLES `tbl_audit_findings_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_audit_findings_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_audit_findings_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_audit_schedule_0_v0s`
--

DROP TABLE IF EXISTS `tbl_audit_schedule_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_audit_schedule_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `notes` text DEFAULT NULL,
  `current_status` int(1) DEFAULT NULL,
  `departments_to_be_audited` text DEFAULT NULL,
  `audit_locations` text DEFAULT NULL,
  `scheduled_end_date` date DEFAULT NULL,
  `schedule_start_date` date DEFAULT NULL,
  `audit_category` varchar(36) DEFAULT NULL,
  `standard` varchar(36) DEFAULT NULL,
  `audit_number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT 'c29a4cae-ee08-4dc8-9e30-ba6bce0005b4',
  `custom_table_id` varchar(36) NOT NULL DEFAULT '28eb2068-cf9d-4dce-8ea6-2347e14b3d60',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `approval_step_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_audit_schedule_0_v0s`
--

LOCK TABLES `tbl_audit_schedule_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_audit_schedule_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_audit_schedule_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_calibration_0_v0s`
--

DROP TABLE IF EXISTS `tbl_calibration_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_calibration_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `calibration_performed_by` varchar(36) DEFAULT NULL,
  `next_calibration_date` date DEFAULT NULL,
  `previous_calibration_date` date DEFAULT NULL,
  `calibration_frequency` varchar(36) DEFAULT NULL,
  `tool_location` varchar(36) DEFAULT NULL,
  `tool_details` varchar(255) DEFAULT NULL,
  `equipement` varchar(255) DEFAULT NULL,
  `number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT 'a9d9fd99-157e-4028-a223-0d72b6d241c8',
  `custom_table_id` varchar(36) NOT NULL DEFAULT 'e60b9ebd-6a04-4caa-975d-c7882a65a704',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_calibration_0_v0s`
--

LOCK TABLES `tbl_calibration_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_calibration_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_calibration_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_customer_complaints_0_v0s`
--

DROP TABLE IF EXISTS `tbl_customer_complaints_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_customer_complaints_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `resolution_details` text DEFAULT NULL,
  `current_status` int(1) DEFAULT NULL,
  `assigned_to` varchar(36) DEFAULT NULL,
  `closure_date` date DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `complaint_details` text DEFAULT NULL,
  `customer` varchar(36) DEFAULT NULL,
  `customer_number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT '888fb842-bc2d-41c4-b17e-7026dd7643a5',
  `custom_table_id` varchar(36) NOT NULL DEFAULT '93b76a14-a8be-43b2-8fd0-eda477388006',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_customer_complaints_0_v0s`
--

LOCK TABLES `tbl_customer_complaints_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_customer_complaints_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_customer_complaints_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_customer_details_0_v0s`
--

DROP TABLE IF EXISTS `tbl_customer_details_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_customer_details_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `customer_type` int(1) DEFAULT NULL,
  `customer_status` int(1) DEFAULT NULL,
  `head_office_address` text DEFAULT NULL,
  `fax` varchar(55) DEFAULT NULL,
  `phone` varchar(55) DEFAULT NULL,
  `official_email` varchar(255) DEFAULT NULL,
  `customer_details` text DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT '06d28e70-8ed6-424d-8a8c-deb73f5f5510',
  `custom_table_id` varchar(36) NOT NULL DEFAULT '06ab4495-44dc-48b3-86da-61f67f492cb7',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_customer_details_0_v0s`
--

LOCK TABLES `tbl_customer_details_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_customer_details_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_customer_details_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_device_equipment_0_v0s`
--

DROP TABLE IF EXISTS `tbl_device_equipment_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_device_equipment_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `maintenance_schedule` varchar(36) DEFAULT NULL,
  `manufacturer_part_number` varchar(255) DEFAULT NULL,
  `equipment_details` text DEFAULT NULL,
  `in_service_since` date DEFAULT NULL,
  `location` varchar(36) DEFAULT NULL,
  `equipment_name` varchar(255) DEFAULT NULL,
  `number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT '5eea859b-aab2-4c26-8af3-abd16bbd02df',
  `custom_table_id` varchar(36) NOT NULL DEFAULT 'b5eaa2ea-e624-4028-887c-c55d3dc1cece',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_device_equipment_0_v0s`
--

LOCK TABLES `tbl_device_equipment_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_device_equipment_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_device_equipment_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_mrm_0_v0s`
--

DROP TABLE IF EXISTS `tbl_mrm_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_mrm_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_details` text DEFAULT NULL,
  `attainted_by` text DEFAULT NULL,
  `actual_meeting_date_time` datetime DEFAULT NULL,
  `meeting_status` int(1) DEFAULT NULL,
  `invitees` text DEFAULT NULL,
  `proposed_by` varchar(36) DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `meeting_number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT 'ea17ca9b-c5ec-4f0d-ad7c-14f534b5f32d',
  `custom_table_id` varchar(36) NOT NULL DEFAULT 'd9385db9-54d3-4569-b625-7507181d00d5',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_mrm_0_v0s`
--

LOCK TABLES `tbl_mrm_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_mrm_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_mrm_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_supplier_details_0_v0s`
--

DROP TABLE IF EXISTS `tbl_supplier_details_0_v0s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_supplier_details_0_v0s` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_company_type` int(1) DEFAULT NULL,
  `supplier_since` date DEFAULT NULL,
  `supplier_email` varchar(255) DEFAULT NULL,
  `supplier_phone` varchar(55) DEFAULT NULL,
  `supplier_address` text DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `supplier_number` varchar(255) NOT NULL,
  `qc_document_id` varchar(36) NOT NULL DEFAULT 'e53215e1-ccea-4017-8fee-bd56f4b9975d',
  `custom_table_id` varchar(36) NOT NULL DEFAULT '395bcbab-a29b-4f92-9dce-e80c5a8ddb96',
  `file_id` varchar(36) DEFAULT NULL,
  `file_key` varchar(50) DEFAULT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `additional_files` text DEFAULT NULL,
  `publish` tinyint(1) DEFAULT 1 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_supplier_details_0_v0s`
--

LOCK TABLES `tbl_supplier_details_0_v0s` WRITE;
/*!40000 ALTER TABLE `tbl_supplier_details_0_v0s` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_supplier_details_0_v0s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usage_details`
--

DROP TABLE IF EXISTS `usage_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_details` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `path` text NOT NULL,
  `file_name` text NOT NULL,
  `last_access` datetime DEFAULT NULL,
  `file_size` text NOT NULL,
  `billed` float(11,2) NOT NULL,
  `db_size` int(11) NOT NULL,
  `db_billed` float(11,2) NOT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL,
  `created` datetime NOT NULL,
  `modified_by` varchar(36) NOT NULL,
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL,
  `soft_delete` tinyint(1) NOT NULL DEFAULT 0,
  `branchid` varchar(36) DEFAULT NULL,
  `departmentid` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_details`
--

LOCK TABLES `usage_details` WRITE;
/*!40000 ALTER TABLE `usage_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `usage_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_access_controls`
--

DROP TABLE IF EXISTS `user_access_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_access_controls` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `users` text DEFAULT NULL,
  `user_access` text NOT NULL,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  `division_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_access_controls`
--

LOCK TABLES `user_access_controls` WRITE;
/*!40000 ALTER TABLE `user_access_controls` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_access_controls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(18) DEFAULT NULL,
  `start_time` datetime DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT current_timestamp(),
  `user_id` varchar(36) DEFAULT NULL,
  `employee_id` varchar(36) DEFAULT NULL,
  `company_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES ('8d4a6249-6564-40bc-805a-43d649348d3b',1,'127.0.0.1','2026-07-06 16:14:19','2026-07-06 16:14:19',NULL,NULL,NULL),('0c627046-433e-44be-8030-2d817fc13fc4',2,'127.0.0.1','2026-07-06 16:15:13','2026-07-06 16:15:13',NULL,NULL,NULL),('af30690c-0b9c-492a-96b8-0bd9ce9152bb',3,'127.0.0.1','2026-07-06 16:16:48','2026-07-06 16:16:48',NULL,NULL,NULL),('fa8530fa-91b1-4841-a4c1-3c0790adc711',4,'127.0.0.1','2026-07-06 16:17:42','2026-07-06 16:17:42','2468b0e4-b6dc-45d8-b117-002868c22d79','54402c6e-7e5d-4572-b34d-2afa43cf2799','11111111-1111-1111-1111-111111111111'),('9aa4787b-8762-40d8-bbe1-2e58dbf3b1b5',5,'127.0.0.1','2026-07-06 16:18:47','2026-07-06 16:18:47','2468b0e4-b6dc-45d8-b117-002868c22d79','54402c6e-7e5d-4572-b34d-2afa43cf2799','11111111-1111-1111-1111-111111111111');
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` varchar(36) NOT NULL,
  `sr_no` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(240) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `is_mr` tinyint(1) DEFAULT 0,
  `is_mt` tinyint(1) DEFAULT NULL,
  `is_hod` tinyint(1) DEFAULT 0,
  `is_view_all` tinyint(1) DEFAULT 0,
  `is_approver` tinyint(1) DEFAULT 0,
  `is_creator` tinyint(1) DEFAULT 1,
  `is_publisher` tinyint(1) DEFAULT 1,
  `status` int(1) DEFAULT 0,
  `department_id` varchar(36) NOT NULL,
  `branch_id` varchar(36) NOT NULL,
  `language_id` varchar(36) DEFAULT '1',
  `login_status` int(1) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `allow_multiple_login` tinyint(1) DEFAULT 0 COMMENT '0= Not allow, 1=Allow',
  `limit_login_attempt` tinyint(1) DEFAULT 1 COMMENT '0= No limit, 1= limit upto 3 attempt',
  `last_activity` datetime DEFAULT NULL,
  `user_access` text DEFAULT NULL,
  `assigned_branches` text DEFAULT NULL,
  `copy_acl_from` varchar(36) DEFAULT NULL,
  `benchmark` int(5) NOT NULL DEFAULT 0,
  `publish` tinyint(1) DEFAULT 0 COMMENT '0=Un 1=Pub',
  `record_status` tinyint(1) DEFAULT 0 COMMENT '0=Un-locked, 1=Locked',
  `status_user_id` varchar(36) DEFAULT NULL,
  `soft_delete` tinyint(1) DEFAULT 0 COMMENT '1=deleted',
  `branchid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `departmentid` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `password_token` varchar(225) DEFAULT NULL,
  `email_token_expires` datetime DEFAULT NULL,
  `pwd_last_modified` datetime DEFAULT NULL,
  `agree` tinyint(1) DEFAULT NULL,
  `division_id` varchar(36) DEFAULT '0',
  `company_id` varchar(36) DEFAULT NULL,
  `created_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `created` datetime NOT NULL COMMENT 'system defined automatically add',
  `modified_by` varchar(36) NOT NULL COMMENT 'system defined automatically add',
  `approved_by` varchar(36) DEFAULT NULL,
  `prepared_by` varchar(36) DEFAULT NULL,
  `modified` datetime NOT NULL COMMENT 'system defined automatically add',
  `system_table_id` varchar(36) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `sr_no` (`sr_no`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('2468b0e4-b6dc-45d8-b117-002868c22d79',1,'54402c6e-7e5d-4572-b34d-2afa43cf2799','Admin','admin@flinkiso.local','f4c12094423e6219463696823acc08dc',1,1,0,1,1,1,1,1,'f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','1',0,'2026-07-06 16:18:47',1,1,'2026-07-06 16:18:47','{}',NULL,'',0,1,0,NULL,0,'fd6cc29d-62bd-4179-ab45-5ce4c0bdf3dc','f8c874f6-bf8f-4599-aa2d-0b6b0791d96c','H6lU-rPw-k',NULL,NULL,0,'0','11111111-1111-1111-1111-111111111111','2468b0e4-b6dc-45d8-b117-002868c22d79','2026-07-06 16:11:37','2468b0e4-b6dc-45d8-b117-002868c22d79',NULL,NULL,'2026-07-06 16:11:37','5297b2e7-0a9c-46e3-96a6-2d8f0a000005');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-06 15:50:40
