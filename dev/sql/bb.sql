SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL COMMENT 'category id',
  `category_name` varchar(255) NOT NULL COMMENT 'category name',
  `category_order` int(11) NOT NULL COMMENT 'order of categories',
  `category_parent_id` int(11) DEFAULT NULL COMMENT 'parent of category',
  `category_active` tinyint(1) NOT NULL COMMENT 'is category active?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `categories` (`category_id`, `category_name`, `category_order`, `category_parent_id`, `category_active`) VALUES
(1, 'TEST CAT', 1, 0, 1),
(2, 'cat 2', 2, 0, 1),
(3, 'CAT 3', 3, 0, 1);

CREATE TABLE `forums` (
  `forum_id` int(11) NOT NULL COMMENT 'forum id',
  `forum_category_id` int(11) NOT NULL COMMENT 'category id of forum',
  `forum_name` varchar(255) NOT NULL COMMENT 'forum name',
  `forum_description` varchar(2048) NOT NULL COMMENT 'description of forum',
  `forum_active` int(11) NOT NULL COMMENT 'is forum active?',
  `forum_parent_id` int(11) NOT NULL COMMENT 'parent of forum',
  `forum_order` int(11) NOT NULL COMMENT 'order of forum',
  `forum_last_topic_id` int(11) NOT NULL COMMENT 'last topic',
  `forum_last_post_id` int(11) NOT NULL COMMENT 'id of last post in this forum',
  `forum_last_post_user_id` int(11) NOT NULL COMMENT 'id of user who add lastet pst',
  `forum_thank` tinyint(1) NOT NULL COMMENT 'forum can thank?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `forums` (`forum_id`, `forum_category_id`, `forum_name`, `forum_description`, `forum_active`, `forum_parent_id`, `forum_order`, `forum_last_topic_id`, `forum_last_post_id`, `forum_last_post_user_id`, `forum_thank`) VALUES
(1, 2, 'TEST FORUM', 'pepiúawdaw', 1, 0, 0, 0, 0, 0, 1),
(2, 1, 'FORUM 2, cat 1 ', '13435', 1, 0, 0, 35, 84, 1, 0),
(3, 2, 'FORUC 1, cat2', '35', 1, 0, 0, 32, 62, 1, 0),
(4, 2, 'Forum 2', 'Forum 2', 1, 3, 0, 0, 66, 1, 0),
(5, 1, 'SUB FORUM test1', '135', 1, 2, 1, 34, 65, 1, 1);

CREATE TABLE `languages` (
  `lang_id` int(11) NOT NULL COMMENT 'lang id',
  `lang_name` varchar(255) NOT NULL COMMENT 'lang name',
  `lang_file_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `languages` (`lang_id`, `lang_name`, `lang_file_name`) VALUES
(1, 'English', 'english'),
(2, 'Čeština', 'czech');

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

INSERT INTO `posts` (`post_id`, `post_topic_id`, `post_forum_id`, `post_user_id`, `post_forum_category_id`, `post_title`, `post_text`, `post_add_time`, `post_edit_count`, `post_last_edit_time`) VALUES
(2, 0, 2, 0, 0, 'N|EW', 'nEWSWWW', 0, 0, 0),
(3, 0, 2, 0, 0, 'N|EW', 'nEWSWWW', 0, 0, 0),
(5, 8, 2, 0, 0, 'TEST LAST TOPIC', 'sadawdawdaw', 0, 0, 0),
(6, 8, 0, 1, 1, 'dawdwawdwa', 'wqdawdawdawd', 0, 0, 0),
(7, 0, 2, 0, 0, 'UUUUUUUUUUUu', 'UUUUUUUUUUUUUUUU', 0, 0, 0),
(8, 0, 2, 0, 0, 'UUUUUUUUUUUu', 'UUUUUUUUUUUUUUUU', 0, 0, 0),
(9, 0, 2, 0, 0, 'PPPPPPPPPPPp', 'PPPPPPPPPPPPPPP', 0, 0, 0),
(10, 0, 2, 1, 0, 'LKKK', 'KKKK', 0, 0, 0),
(11, 0, 2, 1, 0, 'LKKK', 'KJKKK2', 0, 0, 0),
(12, 13, 2, 1, 0, 'CCCCCCCCCC', 'CCCCCCCCCCCCCCCCCC', 0, 0, 0),
(14, 0, 2, 1, 0, 'awdaw', 'wadwadawdawdaw', 0, 0, 0),
(15, 0, 2, 1, 0, 'sssssssss', 'sssssssssssssss', 0, 0, 0),
(17, 8, 2, 1, 0, 'awdawdwa', 'awdawdawdawdawdfawdfawd', 0, 0, 0),
(18, 8, 2, 1, 0, 'awdawd', 'wdawdawdaw', 0, 0, 0),
(19, 8, 2, 1, 0, '', 'ddddddddddddd', 0, 0, 0),
(20, 8, 2, 1, 0, 'awdawd', '45\nawwd\nae', 0, 0, 0),
(21, 8, 2, 1, 0, 'awdwa', 'qdawdaewdawd', 0, 0, 0),
(24, 15, 2, 1, 0, 'f', 'f', 0, 0, 0),
(31, 20, 2, 1, 0, 'awdawd', 'wdadawd\n', 0, 0, 0),
(34, 22, 4, 1, 0, 'prvni téma', 'prvni téma', 0, 0, 0),
(35, 22, 4, 1, 0, 'druhy post', 'druhy post', 0, 0, 0),
(36, 22, 4, 1, 0, 'zzzzzzzzzzz', 'z', 0, 0, 0),
(37, 22, 4, 1, 0, 'ssssss', 'adawdaw', 0, 0, 0),
(38, 22, 4, 1, 0, 'zzz', 'zz', 0, 0, 0),
(39, 22, 4, 1, 0, 'awdw', 'qwwdwad', 0, 0, 0),
(40, 23, 2, 1, 0, 'adaw', 'QDAWDWA', 0, 0, 0),
(41, 24, 2, 1, 0, 'adaw', 'QDAWDWA', 0, 0, 0),
(44, 26, 2, 1, 0, 'awd', 'wwadawdawdwdadwd', 0, 0, 0),
(45, 26, 2, 1, 0, 'ssssssssss', 'wsdadaw', 0, 0, 0),
(46, 26, 2, 1, 0, 'awd', 'wad', 0, 0, 0),
(47, 26, 2, 1, 0, 'awdaw', 'wqdawdaw', 0, 0, 0),
(50, 28, 2, 1, 0, 'awdwa', 'wdwadw', 1517262574, 0, 0),
(51, 29, 2, 1, 0, 'awd', 'awda', 1517265770, 0, 0),
(53, 30, 2, 1, 0, 'awdwa', 'wadwd\n', 1517266244, 0, 0),
(57, 5, 2, 2, 0, 'DWA', 'ASasassssssssssdsc', 1517331108, 4, 1517348008),
(58, 5, 2, 1, 0, 'saf', 'wafafswa', 1517331116, 0, 0),
(59, 23, 2, 1, 0, 'awd', 'awdwad', 1517336059, 0, 0),
(60, 23, 2, 1, 0, 'awdawdw', 'awadaw', 1517336069, 0, 0),
(61, 23, 2, 1, 0, 'awdaw', 'qhlhjkawdaw', 1517336253, 1, 1517336260),
(62, 32, 3, 1, 0, 'awdaw', 'asawd', 1517341976, 0, 0),
(63, 33, 2, 1, 0, 'wwef+weafa;', 'afwa', 1517347734, 0, 0),
(65, 34, 5, 1, 0, 'awdwa', 'awwad', 1517354011, 0, 0),
(66, 22, 4, 1, 0, 'awdawdaw', 'awdawdwadawdwdddddddddddddddd', 1517432134, 0, 0),
(67, 35, 2, 1, 0, 'adaw', 'awwdaw', 1517737194, 0, 0),
(68, 23, 2, 1, 0, 'awd', 'awdwawd', 1517781806, 0, 0),
(69, 23, 2, 1, 0, 'sdaw;', 'awdwa', 1517781815, 0, 0),
(70, 23, 2, 1, 0, 'awdwd', 'sda', 1517781822, 0, 0),
(71, 23, 2, 1, 0, 'awd', 'awdwd', 1517781827, 0, 0),
(72, 23, 2, 1, 0, 'awdw', 'awdw', 1517781833, 0, 0),
(73, 23, 2, 1, 0, 'awd', 'adwa', 1517781838, 0, 0),
(74, 23, 2, 1, 0, 'awdwa', 'wadwd', 1517781845, 0, 0),
(75, 23, 2, 1, 0, 'awdwa', 'wadaw', 1517939870, 0, 0),
(76, 23, 2, 1, 0, 'awd', 'wada', 1517939876, 0, 0),
(77, 23, 2, 1, 0, 'awd', 'awdw', 1517939880, 0, 0),
(78, 23, 2, 1, 0, 'awdaw', 'awdawdaw', 1517939886, 0, 0),
(79, 23, 2, 1, 0, 'awd', 'awdawd', 1517939891, 0, 0),
(80, 23, 2, 1, 0, 'awdwa', 'wawdadwa', 1517939897, 0, 0),
(81, 23, 2, 1, 0, 'awd', 'wawdw', 1517939901, 0, 0),
(82, 23, 2, 1, 0, 'awdw', 'wadawd', 1517939905, 0, 0),
(83, 23, 2, 1, 0, 'awdw', 'waawd', 1517939910, 0, 0),
(84, 23, 2, 1, 0, 'awdwa', 'wadw', 1517939914, 0, 0);

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL COMMENT 'role id',
  `role_name` varchar(255) NOT NULL COMMENT 'role  name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(10, 'Moderator'),
(12, 'awd');

CREATE TABLE `thanks` (
  `thank_id` int(11) NOT NULL COMMENT 'thank id',
  `thank_forum_id` int(11) NOT NULL COMMENT 'forum id',
  `thank_topic_id` int(11) NOT NULL COMMENT 'topic id',
  `thank_user_id` int(11) NOT NULL COMMENT 'user id',
  `thank_time` int(11) NOT NULL COMMENT 'thank time',
  `thank_user_ip` varchar(100) NOT NULL COMMENT 'user IP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `thanks` (`thank_id`, `thank_forum_id`, `thank_topic_id`, `thank_user_id`, `thank_time`, `thank_user_ip`) VALUES
(1, 2, 12, 1, 1517345498, ''),
(2, 2, 5, 1, 1517345899, ''),
(3, 2, 7, 1, 1517345901, ''),
(4, 2, 8, 1, 1517345903, ''),
(5, 2, 9, 1, 1517345906, ''),
(6, 2, 10, 1, 1517345910, ''),
(7, 2, 11, 1, 1517345912, ''),
(8, 2, 13, 1, 1517345923, ''),
(9, 2, 14, 1, 1517345935, ''),
(10, 2, 15, 1, 1517345938, ''),
(11, 2, 17, 1, 1517345942, ''),
(12, 2, 20, 1, 1517345945, ''),
(13, 2, 23, 1, 1517345951, ''),
(14, 2, 24, 1, 1517345953, ''),
(15, 2, 25, 1, 1517345956, ''),
(16, 2, 26, 1, 1517345958, ''),
(17, 2, 27, 1, 1517345961, ''),
(18, 2, 28, 1, 1517345965, ''),
(19, 2, 29, 1, 1517345967, ''),
(20, 2, 33, 1, 1517347739, ''),
(21, 5, 34, 1, 1517354002, ''),
(22, 3, 32, 1, 1517433191, ''),
(23, 2, 35, 1, 1517737200, '');

CREATE TABLE `topics` (
  `topic_id` int(11) NOT NULL COMMENT ' topic id',
  `topic_user_id` int(11) NOT NULL COMMENT 'user id who add this topic',
  `topic_forum_id` int(11) NOT NULL COMMENT 'forum id of this topic',
  `topic_forum_category_id` int(11) NOT NULL COMMENT 'this topic is in category id of forum id',
  `topic_name` varchar(255) NOT NULL COMMENT 'topic name',
  `topic_post_count` int(11) NOT NULL COMMENT 'count of posts in topic',
  `topic_add_time` int(11) NOT NULL COMMENT 'time of add this topic',
  `topic_last_post_id` int(11) NOT NULL COMMENT 'id of last post',
  `topic_last_post_user_id` int(11) NOT NULL COMMENT 'id of user who add add last post in topic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `topics` (`topic_id`, `topic_user_id`, `topic_forum_id`, `topic_forum_category_id`, `topic_name`, `topic_post_count`, `topic_add_time`, `topic_last_post_id`, `topic_last_post_user_id`) VALUES
(5, 2, 2, 0, 'N|EW', 2, 0, 58, 1),
(8, 4165, 2, 0, 'TEST LAST TOPIC', 2, 0, 0, 0),
(9, 8, 2, 0, 'PPPPPPPPPPPp', 1, 0, 0, 0),
(11, 5453, 2, 0, 'LKKK', 1, 0, 0, 0),
(13, 7, 2, 0, 'CCCCCCCCCC', 1, 0, 0, 0),
(15, 54, 2, 0, 'f', 1, 0, 0, 0),
(20, 35, 2, 0, 'awdawd', 1, 0, 0, 0),
(22, 1, 4, 0, 'prvni téma', 7, 0, 66, 1),
(23, 4, 2, 0, 'adaw', 21, 0, 84, 1),
(24, 545, 2, 0, 'adaw', 1, 0, 0, 1),
(26, 6, 2, 0, 'awd', 4, 1517261449, 47, 1),
(28, 345, 2, 0, 'awdwa', 1, 1517262574, 50, 1),
(29, 1, 2, 0, 'awd', 2, 1517265770, 52, 1),
(30, 3535, 2, 0, 'awdwa', 1, 1517266244, 53, 1),
(31, 35, 3, 0, 'awdaw', 1, 1517341948, 0, 1),
(32, 3, 3, 0, 'awdaw', 1, 1517341976, 62, 1),
(33, 1, 2, 0, 'wwef+weafa;', 1, 1517347734, 63, 1),
(34, 1, 5, 0, 'awdw', 2, 1517353999, 65, 1),
(35, 1, 2, 0, 'adaw', 1, 1517737194, 67, 1);

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
  `user_role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`user_id`, `user_name`, `user_password`, `user_email`, `user_signature`, `user_active`, `user_post_count`, `user_topic_count`, `user_thank_count`, `user_lang_id`, `user_role_id`) VALUES
(1, 'user', '$2y$10$cLkcR5WvfrG6DqmYBi0AT./TXWQKNiIYNYCaCRwUrwonByKuVYcsq', 'user@user.com', '', 1, 0, 0, 0, 2, 1);

CREATE TABLE `users2roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'user id',
  `role_id` int(11) NOT NULL COMMENT 'role id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users2roles` (`id`, `user_id`, `role_id`) VALUES
(0, 1, 1);

ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

ALTER TABLE `forums`
  ADD PRIMARY KEY (`forum_id`),
  ADD KEY `forum_category_id` (`forum_category_id`),
  ADD KEY `forum_parent_id` (`forum_parent_id`);

ALTER TABLE `languages`
  ADD PRIMARY KEY (`lang_id`);

ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `post_topic_id` (`post_topic_id`),
  ADD KEY `post_forum_id` (`post_forum_id`),
  ADD KEY `post_user_id` (`post_user_id`),
  ADD KEY `post_forum_category_id` (`post_forum_category_id`);
ALTER TABLE `posts` ADD FULLTEXT KEY `post_title_text` (`post_title`,`post_text`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

ALTER TABLE `thanks`
  ADD PRIMARY KEY (`thank_id`),
  ADD KEY `thank_forum_id` (`thank_forum_id`),
  ADD KEY `thank_topic_id` (`thank_topic_id`),
  ADD KEY `thank_user_id` (`thank_user_id`);

ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `topic_user_id` (`topic_user_id`),
  ADD KEY `topic_forum_id` (`topic_forum_id`),
  ADD KEY `topic_last_post_id` (`topic_last_post_id`),
  ADD KEY `topic_last_post_user_id` (`topic_last_post_user_id`);
ALTER TABLE `topics` ADD FULLTEXT KEY `topic_name` (`topic_name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

ALTER TABLE `users2roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'category id', AUTO_INCREMENT=5;

ALTER TABLE `forums`
  MODIFY `forum_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'forum id', AUTO_INCREMENT=7;

ALTER TABLE `languages`
  MODIFY `lang_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'lang id', AUTO_INCREMENT=3;

ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'post id', AUTO_INCREMENT=85;

ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'role id', AUTO_INCREMENT=13;

ALTER TABLE `thanks`
  MODIFY `thank_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'thank id', AUTO_INCREMENT=24;

ALTER TABLE `topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT COMMENT ' topic id', AUTO_INCREMENT=36;

ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user id', AUTO_INCREMENT=4;
COMMIT;
