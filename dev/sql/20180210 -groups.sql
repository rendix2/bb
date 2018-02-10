CREATE TABLE `bb`.`groups` ( `group_id` INT NOT NULL AUTO_INCREMENT , `group_name` VARCHAR(255) NOT NULL , PRIMARY KEY (`group_id`)) ENGINE = InnoDB;

CREATE TABLE `bb`.`users2firums` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `forum_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`forum_id`)) ENGINE = InnoDB;