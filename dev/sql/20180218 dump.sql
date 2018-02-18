-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Ned 18. úno 2018, 12:53
-- Verze serveru: 10.1.30-MariaDB
-- Verze PHP: 5.6.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `bb`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL COMMENT 'category id',
  `category_name` varchar(255) NOT NULL COMMENT 'category name',
  `category_order` int(11) NOT NULL COMMENT 'order of categories',
  `category_parent_id` int(11) DEFAULT NULL COMMENT 'parent of category',
  `category_active` tinyint(1) NOT NULL COMMENT 'is category active?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_order`, `category_parent_id`, `category_active`) VALUES
(1, 'TEST CAT', 1, 0, 1),
(2, 'cat 2', 2, 0, 1),
(3, 'CAT 3', 3, 0, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `forums`
--

CREATE TABLE `forums` (
  `forum_id` int(11) NOT NULL COMMENT 'forum id',
  `forum_category_id` int(11) NOT NULL COMMENT 'category id of forum',
  `forum_name` varchar(255) NOT NULL COMMENT 'forum name',
  `forum_description` varchar(2048) NOT NULL COMMENT 'description of forum',
  `forum_active` int(11) NOT NULL COMMENT 'is forum active?',
  `forum_parent_id` int(11) NOT NULL COMMENT 'parent of forum',
  `forum_order` int(11) NOT NULL COMMENT 'order of forum',
  `forum_thank` tinyint(1) NOT NULL COMMENT 'forum can thank?',
  `forum_topic_count` int(11) NOT NULL,
  `forum_post_add` tinyint(1) NOT NULL,
  `forum_post_delete` tinyint(1) NOT NULL,
  `forum_post_update` tinyint(1) NOT NULL,
  `forum_topic_add` tinyint(1) NOT NULL,
  `forum_topic_update` tinyint(1) NOT NULL,
  `forum_topic_delete` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `forums`
--

INSERT INTO `forums` (`forum_id`, `forum_category_id`, `forum_name`, `forum_description`, `forum_active`, `forum_parent_id`, `forum_order`, `forum_thank`, `forum_topic_count`, `forum_post_add`, `forum_post_delete`, `forum_post_update`, `forum_topic_add`, `forum_topic_update`, `forum_topic_delete`) VALUES
(1, 2, 'TEST FORUM', 'kj', 1, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0),
(2, 1, 'FORUM 2, cat 1', '13435', 1, 0, 0, 1, 7, 1, 1, 1, 1, 1, 1),
(3, 2, 'FORUC 1, cat2', '35', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, 2, 'Forum 2', 'Forum 2', 1, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5, 1, 'SUB FORUM test1', '135', 1, 2, 1, 1, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `forums2groups`
--

CREATE TABLE `forums2groups` (
  `id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `post_add` tinyint(1) NOT NULL,
  `post_edit` tinyint(1) NOT NULL,
  `post_delete` tinyint(1) NOT NULL,
  `topic_add` tinyint(1) NOT NULL,
  `topic_edit` tinyint(1) NOT NULL,
  `topic_delete` tinyint(1) NOT NULL,
  `topic_thank` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `forums2groups`
--

INSERT INTO `forums2groups` (`id`, `forum_id`, `group_id`, `post_add`, `post_edit`, `post_delete`, `topic_add`, `topic_edit`, `topic_delete`, `topic_thank`) VALUES
(21, 1, 2, 1, 1, 1, 1, 1, 1, 1),
(22, 2, 2, 1, 1, 0, 0, 0, 0, 0),
(23, 3, 2, 0, 0, 0, 0, 0, 0, 0),
(24, 4, 2, 0, 0, 0, 0, 0, 0, 0),
(25, 5, 2, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `groups`
--

INSERT INTO `groups` (`group_id`, `group_name`) VALUES
(2, 'moderátoři'),
(3, 'awdwa');

-- --------------------------------------------------------

--
-- Struktura tabulky `languages`
--

CREATE TABLE `languages` (
  `lang_id` int(11) NOT NULL COMMENT 'lang id',
  `lang_name` varchar(255) NOT NULL COMMENT 'lang name',
  `lang_file_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `languages`
--

INSERT INTO `languages` (`lang_id`, `lang_name`, `lang_file_name`) VALUES
(1, 'English', 'english'),
(2, 'Čeština', 'czech');

-- --------------------------------------------------------

--
-- Struktura tabulky `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL COMMENT 'post id',
  `post_topic_id` int(11) NOT NULL COMMENT 'topic id',
  `post_forum_id` int(11) NOT NULL COMMENT 'forum id',
  `post_user_id` int(11) NOT NULL COMMENT 'user id',
  `post_forum_category_id` int(11) NOT NULL COMMENT 'category id',
  `post_title` varchar(255) NOT NULL COMMENT 'post title',
  `post_text` text NOT NULL COMMENT 'post text',
  `post_add_time` int(11) NOT NULL COMMENT 'time of add this post',
  `post_edit_count` int(11) NOT NULL COMMENT 'count of editations',
  `post_last_edit_time` int(11) NOT NULL COMMENT 'time of last edit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `posts`
--

INSERT INTO `posts` (`post_id`, `post_topic_id`, `post_forum_id`, `post_user_id`, `post_forum_category_id`, `post_title`, `post_text`, `post_add_time`, `post_edit_count`, `post_last_edit_time`) VALUES
(6, 4, 2, 1, 0, 'sdsawdawd', 'adadawdawdawdwadwad', 1518636574, 0, 0),
(7, 4, 2, 1, 0, 'awdw', 'awdawdawdaww', 1518636636, 0, 0),
(8, 4, 2, 1, 0, 'awdaw', 'AWFWADAWDAWD', 1518636642, 0, 0),
(9, 4, 2, 1, 0, 'asdwaw', 'wdaddadda', 1518636655, 0, 0),
(10, 4, 2, 1, 0, 'awdwa', 'wdwdadawdadwadawawdwkůfelfsnwů-466565aewfjklsefljwdwa', 1518636671, 0, 0),
(11, 4, 2, 1, 0, 'wadojůawfdli', 'qwfůokjawdpowad', 1518636702, 0, 0),
(13, 5, 2, 1, 0, 'awdknlwadlij', 'seflkjwadlkjawd', 1518636714, 0, 0),
(14, 5, 2, 1, 0, 'awdknladwlkn', 'qasefkljawdlksad', 1518636719, 0, 0),
(15, 5, 2, 1, 0, 'wadůkmawdůkmwad', 'qlůkwdaůlkawdlůmawd', 1518636731, 0, 0),
(16, 5, 2, 1, 0, 'sdůkmawdklům', 'qaeflůkmwadůlkadw', 1518636736, 0, 0),
(17, 6, 2, 1, 0, 'awdlknawdlk', 'awdlkmawdlkwad', 1518636785, 0, 0),
(18, 6, 2, 1, 0, 'awdkůjawdlk', 'SKLFWADLKWAD', 1518636791, 0, 0),
(19, 6, 2, 1, 0, 'wdalkůdwaůlkQŮO', 'ŮOQŮPOWADŮKOAWDOIPJWADF', 1518636798, 0, 0),
(20, 6, 2, 1, 0, 'AWDLOŮADWŮO', 'QESPOFEAFPŮQq', 1518636803, 0, 0),
(21, 6, 2, 1, 0, 'wadkůjdawpoů', 'qpofeapowadepowae', 1518636809, 0, 0),
(22, 6, 2, 1, 0, 'awdůdwaůo', 'oPkqoaefj§esúífujw§wadwdal§kwapoj¨p§fas', 1518636817, 0, 0),
(23, 6, 2, 1, 0, 'awdpojawdjpoi', 'pojfesapoijůawd', 1518636825, 0, 0),
(24, 6, 2, 1, 0, 'wadoůjdwaij', 'qipjadwpiojwadilwd', 1518636830, 0, 0),
(25, 6, 2, 1, 0, 'wdlkjkawdilk', 'ijdwaikawdl', 1518636837, 0, 0),
(26, 6, 2, 1, 0, 'awdkůljldawkljů', 'qkijfadeikdfas', 1518636843, 0, 0),
(27, 6, 2, 1, 0, 'wdlkndawlk', 'qkldwlkdw', 1518636847, 0, 0),
(28, 6, 2, 1, 0, 'wdkůldwaůkj', 'qkljldwalkwad', 1518636852, 0, 0),
(29, 6, 2, 1, 0, 'swdlkneafkln', 'qlkwda', 1518636861, 0, 0),
(30, 7, 2, 1, 0, 'awdkladwlkj', 'LKDWALIDWA', 1518636878, 0, 0),
(31, 7, 2, 1, 0, 'WADJHWADOIUHU', 'quoiadsujhwad', 1518636891, 0, 0),
(32, 7, 2, 1, 0, 'dwakjadwklj', 'qlkdwaliwad', 1518636897, 0, 0),
(33, 7, 2, 1, 0, 'adaedkůdawkl', 'KLDAFLKAWD', 1518636906, 0, 0),
(34, 4, 2, 1, 0, 'wad', 'qwsadwadwa', 1518719937, 0, 0),
(35, 4, 2, 1, 0, 'aawd', 'qwdaewfaw', 1518727590, 0, 0),
(36, 8, 2, 1, 0, 'awd', 'wadawd', 1518871298, 0, 0),
(37, 9, 2, 1, 0, 'test', 'tawddaw', 1518871675, 0, 0),
(38, 10, 2, 1, 0, 'test', 'tawddaw', 1518871713, 0, 0),
(39, 11, 2, 1, 0, 'test', 'tawddaw', 1518871728, 0, 0),
(40, 12, 2, 1, 0, 'test', 'tawddaw', 1518871750, 0, 0),
(41, 13, 2, 1, 0, 'test', 'tawddaw', 1518871760, 0, 0),
(42, 14, 2, 1, 0, 'test', 'tawddaw', 1518871796, 0, 0),
(43, 15, 2, 1, 0, 'test', 'tawddaw', 1518871825, 0, 0),
(44, 16, 2, 1, 0, 'test', 'tawddaw', 1518871849, 0, 0),
(45, 17, 2, 1, 0, 'test', 'tawddaw', 1518871910, 0, 0),
(46, 18, 2, 1, 0, 'test', 'tawddaw', 1518871930, 0, 0),
(47, 6, 2, 1, 0, 'adwa', 'wdwaw', 1518905068, 0, 0),
(48, 6, 2, 1, 0, 'awd', 'wadawdawdawdwa', 1518905209, 0, 0),
(49, 6, 2, 1, 0, 'adqa', 'aqdawdw', 1518905253, 0, 0),
(50, 6, 2, 1, 0, 'awdaw', 'qwadadwdaw', 1518905309, 0, 0),
(51, 8, 2, 1, 0, 'awda', 'wwdawdwd', 1518905391, 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `ranks`
--

CREATE TABLE `ranks` (
  `rank_id` int(11) NOT NULL,
  `rank_name` varchar(255) NOT NULL,
  `rank_file` varchar(255) DEFAULT NULL,
  `rank_from` int(11) DEFAULT NULL,
  `rank_to` int(11) DEFAULT NULL,
  `rank_special` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `ranks`
--

INSERT INTO `ranks` (`rank_id`, `rank_name`, `rank_file`, `rank_from`, `rank_to`, `rank_special`) VALUES
(2, 'role 1', 'e98ef1b7c7d380e.jpg', 0, 0, 1),
(3, 'role 2', NULL, 5, 9, 0),
(4, 'role 3', NULL, 10, 20, 0),
(5, 'role 4', 'e3835046e383400.jpg', 21, 28, 0),
(6, 'role 5', NULL, 29, 45, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL COMMENT 'report id',
  `report_user_id` int(11) NOT NULL COMMENT 'reporter user id',
  `report_forum_id` int(11) NOT NULL,
  `report_topic_id` int(11) NOT NULL COMMENT 'reported topic id',
  `report_post_id` int(11) NOT NULL COMMENT 'reported post id',
  `report_text` text NOT NULL COMMENT 'reports text',
  `report_time` int(11) NOT NULL COMMENT 'report time',
  `report_status` int(11) NOT NULL COMMENT 'report status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='reports';

--
-- Vypisuji data pro tabulku `reports`
--

INSERT INTO `reports` (`report_id`, `report_user_id`, `report_forum_id`, `report_topic_id`, `report_post_id`, `report_text`, `report_time`, `report_status`) VALUES
(1, 0, 0, 0, 0, '', 0, 0),
(2, 1, 2, 4, 35, '', 0, 0),
(3, 1, 2, 4, 35, 'awdawawd', 0, 0),
(4, 1, 2, 4, 35, 'awdawawd', 0, 0),
(5, 1, 2, 4, 35, 'awdwadwawdawawddwadwadawdawdawdawdawd', 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `session_key` varchar(255) NOT NULL,
  `session_from` int(11) NOT NULL,
  `session_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `thanks`
--

CREATE TABLE `thanks` (
  `thank_id` int(11) NOT NULL COMMENT 'thank id',
  `thank_forum_id` int(11) NOT NULL COMMENT 'forum id',
  `thank_topic_id` int(11) NOT NULL COMMENT 'topic id',
  `thank_user_id` int(11) NOT NULL COMMENT 'user id',
  `thank_time` int(11) NOT NULL COMMENT 'thank time',
  `thank_user_ip` varchar(100) NOT NULL COMMENT 'user IP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `thanks`
--

INSERT INTO `thanks` (`thank_id`, `thank_forum_id`, `thank_topic_id`, `thank_user_id`, `thank_time`, `thank_user_ip`) VALUES
(15, 2, 25, 1, 1517345956, ''),
(20, 2, 33, 1, 1517347739, ''),
(21, 5, 34, 1, 1517354002, ''),
(22, 3, 32, 1, 1517433191, ''),
(23, 2, 35, 1, 1517737200, ''),
(24, 2, 38, 1, 1518042907, ''),
(25, 2, 43, 1, 1518044699, ''),
(26, 2, 48, 1, 1518046084, ''),
(27, 1, 52, 1, 1518047483, ''),
(35, 2, 4, 1, 1518636647, ''),
(36, 2, 6, 1, 1518636865, ''),
(37, 2, 7, 1, 1518636883, ''),
(38, 2, 8, 1, 1518905352, '');

-- --------------------------------------------------------

--
-- Struktura tabulky `topics`
--

CREATE TABLE `topics` (
  `topic_id` int(11) NOT NULL COMMENT ' topic id',
  `topic_user_id` int(11) NOT NULL COMMENT 'user id who add this topic',
  `topic_forum_id` int(11) NOT NULL COMMENT 'forum id of this topic',
  `topic_forum_category_id` int(11) NOT NULL COMMENT 'this topic is in category id of forum id',
  `topic_name` varchar(255) NOT NULL COMMENT 'topic name',
  `topic_post_count` int(11) NOT NULL COMMENT 'count of posts in topic',
  `topic_add_time` int(11) NOT NULL COMMENT 'time of add this topic',
  `topic_locked` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `topics`
--

INSERT INTO `topics` (`topic_id`, `topic_user_id`, `topic_forum_id`, `topic_forum_category_id`, `topic_name`, `topic_post_count`, `topic_add_time`, `topic_locked`) VALUES
(4, 1, 2, 0, 'sdsawdawd', 8, 0, 0),
(5, 1, 2, 0, 'awdknlwadlij', 4, 0, 0),
(6, 1, 2, 0, 'awdlknawdlk', 17, 0, 0),
(7, 1, 2, 0, 'awdkladwlkj', 4, 0, 0),
(8, 1, 2, 0, 'awd', 2, 0, 0),
(9, 1, 2, 0, 'test', 1, 0, 0),
(10, 1, 2, 0, 'test', 1, 0, 0),
(11, 1, 2, 0, 'test', 1, 0, 0),
(12, 1, 2, 0, 'test', 1, 0, 0),
(13, 1, 2, 0, 'test', 1, 0, 0),
(14, 1, 2, 0, 'test', 1, 0, 0),
(15, 1, 2, 0, 'test', 1, 0, 0),
(16, 1, 2, 0, 'test', 1, 0, 0),
(17, 1, 2, 0, 'test', 1, 0, 0),
(18, 1, 2, 0, 'test', 1, 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `topics_watch`
--

CREATE TABLE `topics_watch` (
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `topics_watch`
--

INSERT INTO `topics_watch` (`topic_id`, `user_id`) VALUES
(6, 1),
(6, 2),
(8, 1),
(18, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL COMMENT 'user id',
  `user_name` varchar(255) NOT NULL COMMENT 'user name',
  `user_password` varchar(512) NOT NULL COMMENT 'hash of users password',
  `user_email` varchar(255) NOT NULL COMMENT 'user email',
  `user_signature` text NOT NULL COMMENT 'user signature',
  `user_active` tinyint(1) NOT NULL COMMENT 'is user active?',
  `user_post_count` int(11) NOT NULL COMMENT 'count of users posts',
  `user_topic_count` int(11) NOT NULL COMMENT 'count of users topics',
  `user_thank_count` int(11) NOT NULL COMMENT 'count of thanks',
  `user_lang_id` int(11) NOT NULL COMMENT 'lang_id',
  `user_role_id` int(11) NOT NULL,
  `user_avatar` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `users2forums`
--

CREATE TABLE `users2forums` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `users2forums`
--

INSERT INTO `users2forums` (`id`, `user_id`, `forum_id`) VALUES
(7, 1, 1),
(8, 1, 2),
(10, 1, 3),
(11, 1, 4),
(9, 1, 5);

-- --------------------------------------------------------

--
-- Struktura tabulky `users2groups`
--

CREATE TABLE `users2groups` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `users2groups`
--

INSERT INTO `users2groups` (`id`, `group_id`, `user_id`) VALUES
(70, 2, 1),
(74, 2, 2),
(76, 2, 3),
(71, 3, 1),
(75, 3, 2),
(77, 3, 3);

-- --------------------------------------------------------

--
-- Struktura tabulky `users2sessions`
--

CREATE TABLE `users2sessions` (
  `user_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `category_parent_id` (`category_parent_id`);

--
-- Klíče pro tabulku `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`forum_id`),
  ADD KEY `forum_category_id` (`forum_category_id`),
  ADD KEY `forum_parent_id` (`forum_parent_id`);

--
-- Klíče pro tabulku `forums2groups`
--
ALTER TABLE `forums2groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `forum_id_2_group_id` (`forum_id`,`group_id`) USING BTREE,
  ADD KEY `forum_id` (`forum_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Klíče pro tabulku `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Klíče pro tabulku `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`lang_id`);

--
-- Klíče pro tabulku `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `post_topic_id` (`post_topic_id`),
  ADD KEY `post_forum_id` (`post_forum_id`),
  ADD KEY `post_user_id` (`post_user_id`),
  ADD KEY `post_forum_category_id` (`post_forum_category_id`),
  ADD KEY `post_topic_id_2` (`post_topic_id`,`post_forum_id`);
ALTER TABLE `posts` ADD FULLTEXT KEY `post_title_text` (`post_title`,`post_text`);

--
-- Klíče pro tabulku `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`rank_id`);

--
-- Klíče pro tabulku `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Klíče pro tabulku `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Klíče pro tabulku `thanks`
--
ALTER TABLE `thanks`
  ADD PRIMARY KEY (`thank_id`),
  ADD KEY `thank_forum_id` (`thank_forum_id`),
  ADD KEY `thank_topic_id` (`thank_topic_id`),
  ADD KEY `thank_user_id` (`thank_user_id`);

--
-- Klíče pro tabulku `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `topic_user_id` (`topic_user_id`),
  ADD KEY `topic_forum_id` (`topic_forum_id`);
ALTER TABLE `topics` ADD FULLTEXT KEY `topic_name` (`topic_name`);

--
-- Klíče pro tabulku `topics_watch`
--
ALTER TABLE `topics_watch`
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `topic_id_2` (`topic_id`,`user_id`);

--
-- Klíče pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- Klíče pro tabulku `users2forums`
--
ALTER TABLE `users2forums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `forum_id` (`forum_id`),
  ADD KEY `user_id_2` (`user_id`,`forum_id`);

--
-- Klíče pro tabulku `users2groups`
--
ALTER TABLE `users2groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id_2` (`group_id`,`user_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'category id', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `forums`
--
ALTER TABLE `forums`
  MODIFY `forum_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'forum id', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `forums2groups`
--
ALTER TABLE `forums2groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pro tabulku `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `languages`
--
ALTER TABLE `languages`
  MODIFY `lang_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'lang id', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'post id', AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pro tabulku `ranks`
--
ALTER TABLE `ranks`
  MODIFY `rank_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'report id', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `thanks`
--
ALTER TABLE `thanks`
  MODIFY `thank_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'thank id', AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pro tabulku `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT COMMENT ' topic id', AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user id', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `users2forums`
--
ALTER TABLE `users2forums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pro tabulku `users2groups`
--
ALTER TABLE `users2groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
