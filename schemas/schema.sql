CREATE TABLE `banks`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `name`              VARCHAR(100),
    `ag`                VARCHAR(10),
    `cc`                VARCHAR(10),
    `type`              VARCHAR(100),
    `balance`           VARCHAR(100),
    `manager_name`      VARCHAR(100),
    `manager_email`     VARCHAR(100),
    `manager_phone`     VARCHAR(100),
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT    `banks_fulltext_key` (`name`, `ag`, `cc`, `manager_name`, `manager_email`)
);

CREATE TABLE `clubs`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `name`              VARCHAR(100),
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT    `clubs_fulltext_key` (`name`)
);

CREATE TABLE `transactions`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `player_id`         INTEGER,
    `operator_id`       INTEGER,
    `bank_id`           INTEGER,
    `club_id`           INTEGER,
    `status_id`         INTEGER,
    `value`             INTEGER,
    `type`              VARCHAR(100),
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY         `transactions_type_key` (`type`)
);

CREATE TABLE `transactions_status`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `name`              VARCHAR(100),
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT    `transactions_status_fulltext_key` (`name`)
);

CREATE TABLE `players`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `email`             VARCHAR(255) NOT NULL,
    `nick`              VARCHAR(255) NOT NULL,
    `password`          VARCHAR(255),
    `name`              VARCHAR(255) NOT NULL,
    `avatar`            VARCHAR(255),
    `agent_id`          INTEGER,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY         `players_nick_key` (`nick`),
    KEY         `players_email_key` (`email`),
    FULLTEXT    `players_fulltext_key` (`name`, `nick`, `email`)
);

CREATE TABLE `password_resets`
(
    `email`             VARCHAR(255),
    `token`             VARCHAR(255) PRIMARY KEY,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY `password_resets_token_key` (`token`)
);

CREATE TABLE `user_access`
(
    `id`                INTEGER,
    `email`             VARCHAR(255),
    `ip`                VARCHAR(255),
    `platform`          VARCHAR(255),
    `date`              DATETIME,
    KEY `user_access_email_key` (`email`)
);

CREATE TABLE `users`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `email`             VARCHAR(255) NOT NULL,
    `nick`              VARCHAR(255) NOT NULL,
    `password`          VARCHAR(255),
    `name`              VARCHAR(255) NOT NULL,
    `avatar`            VARCHAR(255),
    `role`              VARCHAR(30) NOT NULL,
    `active`            BOOLEAN,
    `login_attempts`    TINYINT(3),
    `last_login`        DATETIME,
    `block_expires`     DATETIME,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY         `users_nick_key` (`nick`),
    KEY         `users_email_key` (`email`),
    KEY         `users_role_key` (`role`),
    FULLTEXT    `users_fulltext_key` (`name`, `nick`, `email`)
);

ALTER TABLE `transactions` ADD FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);
ALTER TABLE `transactions` ADD FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`);
ALTER TABLE `transactions` ADD FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`);
ALTER TABLE `transactions` ADD FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`);
ALTER TABLE `transactions` ADD FOREIGN KEY (`status_id`) REFERENCES `transactions_status` (`id`);

ALTER TABLE `players` ADD FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`);
