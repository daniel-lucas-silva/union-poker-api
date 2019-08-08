CREATE TABLE `logs`
(
    `id`    integer PRIMARY KEY AUTO_INCREMENT,
    `email` varchar(255),
    `route` varchar(255),
    `date`  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY     `logs_email_key` (`email`)
);