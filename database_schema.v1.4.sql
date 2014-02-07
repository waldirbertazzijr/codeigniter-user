-- --------------------------------------------------------
-- Author: Waldir Bertazzi Junior
-- Version: 1.4
-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `users_meta`
--

CREATE TABLE IF NOT EXISTS `users_meta` (
  `user_id` mediumint(8) NOT NULL,
  `name` varchar(40) NOT NULL,
  `value` varchar(255) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_meta`
--

INSERT INTO `users_meta` (`user_id`, `name`, `value`) VALUES
(1, 'adress_street', 'Wall Street'),
(1, 'address_number', '134'),
(1, 'street_state', 'NY');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users_meta`
--
ALTER TABLE `users_meta`
	ADD CONSTRAINT `users_meta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `users_permissions`
--
ALTER TABLE `users_permissions`
	ADD CONSTRAINT `users_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
	ADD CONSTRAINT `users_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;