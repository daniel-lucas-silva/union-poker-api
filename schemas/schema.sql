CREATE TABLE `categories`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `parent_id`         INTEGER,
    `children_count`    TINYINT(3),
    `name`              VARCHAR(100),
    `slug`              VARCHAR(100),
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY  `categories_slug_unique_key` (`slug`),
    FULLTEXT    `categories_fulltext_key` (`name`)
);


CREATE TABLE `images`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `path`              VARCHAR(255),
    `name`              VARCHAR(255),
    `imageable_id`      INTEGER,
    `imageable_type`    ENUM('posts'),
    `order`             TINYINT,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `images_imageable_id_key` (`imageable_id`),
    KEY `images_imageable_type_key` (`imageable_type`)
);

CREATE TABLE `password_resets`
(
    `email`             VARCHAR(255),
    `token`             VARCHAR(255) PRIMARY KEY,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY `password_resets_token_key` (`token`)
);

CREATE TABLE `searches`
(
    `id`                INTEGER PRIMARY KEY AUTO_INCREMENT,
    `user_id`           INTEGER(5),
    `query`             TEXT,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP
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
    `username`          VARCHAR(255) NOT NULL,
    `password`          VARCHAR(255),
    `name`              VARCHAR(255) NOT NULL,
    `avatar`            VARCHAR(255),
    `role`              VARCHAR(255) NOT NULL,
    `active`            BOOLEAN,
    `login_attempts`    TINYINT(3),
    `last_login`        DATETIME,
    `block_expires`     DATETIME,
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY         `users_username_key` (`username`),
    KEY         `users_email_key` (`email`),
    FULLTEXT    `users_fulltext_key` (`name`, `username`, `email`)
);

ALTER TABLE `categories` ADD FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

ALTER TABLE `searches` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
