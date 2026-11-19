-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pát 29. čen 2018, 21:05
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
-- Struktura tabulky `bans`
--

CREATE TABLE `bans` (
  `ban_id` int(11) NOT NULL COMMENT 'ban id',
  `ban_email` varchar(255) NOT NULL COMMENT 'banned email',
  `ban_ip` varchar(255) NOT NULL COMMENT 'banned ip',
  `ban_user_name` varchar(255) NOT NULL COMMENT 'banned user name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL COMMENT 'category id',
  `category_name` varchar(255) NOT NULL COMMENT 'category name',
  `category_order` int(11) NOT NULL COMMENT 'order of categories',
  `category_parent_id` int(11) DEFAULT NULL COMMENT 'parent of category',
  `category_active` tinyint(1) NOT NULL COMMENT 'is category active?',
  `category_left` int(11) NOT NULL COMMENT 'mptt_left',
  `category_right` int(11) NOT NULL COMMENT 'mptt_right'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `forum_topic_count` int(11) NOT NULL COMMENT 'topic count',
  `forum_post_add` tinyint(1) NOT NULL COMMENT 'can add posts',
  `forum_post_delete` tinyint(1) NOT NULL COMMENT 'can delete posts by self',
  `forum_post_update` tinyint(1) NOT NULL COMMENT 'can post update',
  `forum_topic_add` tinyint(1) NOT NULL COMMENT 'can add topic',
  `forum_topic_update` tinyint(1) NOT NULL COMMENT 'can update topic',
  `forum_topic_delete` tinyint(1) NOT NULL COMMENT 'can delete topic',
  `forum_fast_reply` int(11) NOT NULL COMMENT 'can add fast reply (post)',
  `forum_rules` text NOT NULL COMMENT 'rules of forum',
  `forum_left` int(255) NOT NULL COMMENT 'mptt_left',
  `forum_right` int(255) NOT NULL COMMENT 'mptt_right'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `forums2groups`
--

CREATE TABLE `forums2groups` (
  `id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL COMMENT 'forum id',
  `group_id` int(11) NOT NULL COMMENT 'group id',
  `post_add` tinyint(1) NOT NULL COMMENT 'can add post',
  `post_edit` tinyint(1) NOT NULL COMMENT 'can edit post',
  `post_delete` tinyint(1) NOT NULL COMMENT 'can delete post',
  `topic_add` tinyint(1) NOT NULL COMMENT 'can add topic',
  `topic_edit` tinyint(1) NOT NULL COMMENT 'can edit topic',
  `topic_delete` tinyint(1) NOT NULL COMMENT 'can delete topic',
  `topic_thank` tinyint(1) NOT NULL COMMENT 'can thank topic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='say which group has access into forum';

-- --------------------------------------------------------

--
-- Struktura tabulky `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL COMMENT 'group id',
  `group_name` varchar(255) NOT NULL COMMENT 'group name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='groups';

-- --------------------------------------------------------

--
-- Struktura tabulky `languages`
--

CREATE TABLE `languages` (
  `lang_id` int(11) NOT NULL COMMENT 'lang id',
  `lang_name` varchar(255) NOT NULL COMMENT 'lang name',
  `lang_file_name` varchar(255) NOT NULL COMMENT 'name of lang file'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='languages';

-- --------------------------------------------------------

--
-- Struktura tabulky `mails`
--

CREATE TABLE `mails` (
  `mail_id` int(11) NOT NULL COMMENT 'mail id',
  `mail_from` varchar(255) DEFAULT NULL COMMENT 'mail from',
  `mail_to` text NOT NULL COMMENT 'mail recepients',
  `mail_subject` varchar(255) NOT NULL COMMENT 'mail suibject',
  `mail_time` int(11) NOT NULL COMMENT 'mail sent time',
  `mail_text` text NOT NULL COMMENT 'mail text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='history of sent mails';

-- --------------------------------------------------------

--
-- Struktura tabulky `moderators`
--

CREATE TABLE `moderators` (
  `moderator_id` int(11) NOT NULL COMMENT 'moderator id',
  `user_id` int(11) NOT NULL COMMENT 'user id',
  `forum_id` int(11) NOT NULL COMMENT 'forum id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='moderators of forum';

-- --------------------------------------------------------

--
-- Struktura tabulky `pm`
--

CREATE TABLE `pm` (
  `pm_id` int(255) NOT NULL COMMENT 'pm id',
  `pm_user_id_from` int(255) NOT NULL COMMENT 'pm user id from',
  `pm_user_id_to` int(255) NOT NULL COMMENT 'pm user id to',
  `pm_subject` varchar(255) NOT NULL COMMENT 'pm subject',
  `pm_text` text NOT NULL COMMENT 'pm text',
  `pm_status` enum('sent','unread','read','') NOT NULL COMMENT 'pm status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='private messages';

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
  `post_add_user_ip` varchar(255) NOT NULL COMMENT 'ip  address of poster',
  `post_edit_user_ip` varchar(255) NOT NULL,
  `post_edit_count` int(11) NOT NULL COMMENT 'count of editations',
  `post_last_edit_time` int(11) NOT NULL COMMENT 'time of last edit',
  `post_locked` tinyint(1) NOT NULL COMMENT 'locked post - could not be edited'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='posts';

-- --------------------------------------------------------

--
-- Struktura tabulky `ranks`
--

CREATE TABLE `ranks` (
  `rank_id` int(11) NOT NULL COMMENT 'rank id',
  `rank_name` varchar(255) NOT NULL COMMENT 'rank name',
  `rank_file` varchar(255) DEFAULT NULL COMMENT 'file name of rank image',
  `rank_from` int(11) DEFAULT NULL COMMENT 'rank from number of posts',
  `rank_to` int(11) DEFAULT NULL COMMENT 'rank to number of posts',
  `rank_special` tinyint(1) NOT NULL COMMENT 'is rank special? (no using rank_to and rank_from)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ranks';

-- --------------------------------------------------------

--
-- Struktura tabulky `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL COMMENT 'report id',
  `report_user_id` int(11) NOT NULL COMMENT 'reporter user id',
  `report_forum_id` int(11) NOT NULL COMMENT 'report forum id',
  `report_topic_id` int(11) NOT NULL COMMENT 'reported topic id',
  `report_post_id` int(11) DEFAULT NULL COMMENT 'reported post id',
  `report_text` text NOT NULL COMMENT 'reports text',
  `report_time` int(11) NOT NULL COMMENT 'report time',
  `report_status` int(11) NOT NULL COMMENT 'report status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='reports';

-- --------------------------------------------------------

--
-- Struktura tabulky `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL COMMENT 'session id for this table',
  `session_user_id` int(11) NOT NULL COMMENT 'user id',
  `session_key` varchar(255) NOT NULL COMMENT 'session id generated by php',
  `session_from` int(11) NOT NULL COMMENT 'when session started',
  `session_last_activity` int(11) NOT NULL COMMENT 'when was last page reload'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sessions';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tanks for topics';

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
  `topic_locked` tinyint(1) NOT NULL COMMENT 'is topic locked?',
  `topic_view_count` int(11) NOT NULL COMMENT 'count of view topic'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `topics_watch`
--

CREATE TABLE `topics_watch` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL COMMENT 'topic id',
  `user_id` int(11) NOT NULL COMMENT 'user id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `user_watch_count` int(11) NOT NULL COMMENT 'count of watched topics',
  `user_lang_id` int(11) NOT NULL COMMENT 'lang_id',
  `user_role_id` int(11) NOT NULL COMMENT 'role id',
  `user_avatar` varchar(512) DEFAULT NULL COMMENT 'file name of users avatar',
  `user_register_time` int(11) NOT NULL COMMENT 'time when users registration was done',
  `user_last_login_time` int(11) NOT NULL COMMENT 'user last login time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `users2forums`
--

CREATE TABLE `users2forums` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'user id',
  `forum_id` int(11) NOT NULL COMMENT 'forum id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='says who can into which forum';

-- --------------------------------------------------------

--
-- Struktura tabulky `users2groups`
--

CREATE TABLE `users2groups` (
  `id` int(11) NOT NULL COMMENT 'users2groups id',
  `group_id` int(11) NOT NULL COMMENT 'group id',
  `user_id` int(11) NOT NULL COMMENT 'user id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='says members of group';

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `bans`
--
ALTER TABLE `bans`
  ADD PRIMARY KEY (`ban_id`);

--
-- Klíče pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `category_parent_id` (`category_parent_id`),
  ADD KEY `category_left` (`category_left`);

--
-- Klíče pro tabulku `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`forum_id`),
  ADD KEY `forum_category_id` (`forum_category_id`),
  ADD KEY `forum_parent_id` (`forum_parent_id`),
  ADD KEY `forum_left` (`forum_left`);

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
-- Klíče pro tabulku `mails`
--
ALTER TABLE `mails`
  ADD PRIMARY KEY (`mail_id`);

--
-- Klíče pro tabulku `moderators`
--
ALTER TABLE `moderators`
  ADD PRIMARY KEY (`moderator_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `forum_id` (`forum_id`);

--
-- Klíče pro tabulku `pm`
--
ALTER TABLE `pm`
  ADD PRIMARY KEY (`pm_id`),
  ADD KEY `pm_user_id_from` (`pm_user_id_from`),
  ADD KEY `pm_user_id_to` (`pm_user_id_to`);

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
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `session_user_id` (`session_user_id`),
  ADD KEY `session_key` (`session_key`);

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
  ADD PRIMARY KEY (`id`),
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
-- AUTO_INCREMENT pro tabulku `bans`
--
ALTER TABLE `bans`
  MODIFY `ban_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ban id', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'category id', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `forums`
--
ALTER TABLE `forums`
  MODIFY `forum_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'forum id', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pro tabulku `forums2groups`
--
ALTER TABLE `forums2groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pro tabulku `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'group id', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `languages`
--
ALTER TABLE `languages`
  MODIFY `lang_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'lang id', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `mails`
--
ALTER TABLE `mails`
  MODIFY `mail_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'mail id', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pro tabulku `moderators`
--
ALTER TABLE `moderators`
  MODIFY `moderator_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'moderator id', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pro tabulku `pm`
--
ALTER TABLE `pm`
  MODIFY `pm_id` int(255) NOT NULL AUTO_INCREMENT COMMENT 'pm id';

--
-- AUTO_INCREMENT pro tabulku `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'post id', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `ranks`
--
ALTER TABLE `ranks`
  MODIFY `rank_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'rank id', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'report id', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pro tabulku `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'session id for this table', AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT pro tabulku `thanks`
--
ALTER TABLE `thanks`
  MODIFY `thank_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'thank id', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT COMMENT ' topic id', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `topics_watch`
--
ALTER TABLE `topics_watch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user id', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `users2forums`
--
ALTER TABLE `users2forums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `users2groups`
--
ALTER TABLE `users2groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'users2groups id', AUTO_INCREMENT=78;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
