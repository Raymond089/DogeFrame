CREATE TABLE `doge_transactions` (
`transaction_id` int(11) NOT NULL AUTO_INCREMENT,
`send_user` int(11) DEFAULT '0',
`receive_user` int(11) DEFAULT '0',
`amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
`time` TEXT,
`txID` TEXT,
`status` varchar(1) DEFAULT NULL,
PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB;
