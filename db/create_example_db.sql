CREATE TABLE `usertable` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `doge_spend` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `doge_received` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `doge_withdraw` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `doge_available` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `doge_deposit` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `doge_address` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;
