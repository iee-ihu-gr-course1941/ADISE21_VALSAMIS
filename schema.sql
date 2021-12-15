DROP TABLE IF EXISTS `board`;
CREATE TABLE `board` (
  `x` tinyint(1) NOT NULL,
  `y` tinyint(1) NOT NULL,
  `piece` int(12) DEFAULT NULL,
  PRIMARY KEY (`x`,`y`)
)
INSERT INTO `board` VALUES  (1,1,NULL),
                            (1,2,NULL),
                            (1,3,NULL),
                            (1,4,NULL),
                            (2,1,NULL),
                            (2,2,NULL),
                            (2,3,NULL),
                            (2,4,NULL),
                            (3,1,NULL),
                            (3,2,NULL),
                            (3,3,NULL),
                            (3,4,NULL),
                            (4,1,NULL),
                            (4,2,NULL),
                            (4,3,NULL),
                            (4,4,NULL);


DROP TABLE IF EXISTS `board_empty`;
CREATE TABLE `board_empty` (
  `x` tinyint(1) NOT NULL,
  `y` tinyint(1) NOT NULL,
  `piece` int(12) DEFAULT NULL,
  PRIMARY KEY (`x`,`y`)
)

  INSERT INTO `board_empty` VALUES  (1,1,NULL),
                                    (1,2,NULL),
                                    (1,3,NULL),
                                    (1,4,NULL),
                                    (2,1,NULL),
                                    (2,2,NULL),
                                    (2,3,NULL),
                                    (2,4,NULL),
                                    (3,1,NULL),
                                    (3,2,NULL),
                                    (3,3,NULL),
                                    (3,4,NULL),
                                    (4,1,NULL),
                                    (4,2,NULL),
                                    (4,3,NULL),
                                    (4,4,NULL);

DROP TABLE IF EXISTS `game_status`;
CREATE TABLE `game_status` (
  `status` enum('not active','initialized','started','ended','aborted') NOT NULL DEFAULT 'not active',
  `player_turn` varchar(100) DEFAULT NULL,
  `current_piece` int(12) DEFAULT NULL,
  `result` enum('W','D') DEFAULT NULL,
  `win_comb` varchar(20) DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT NULL
)

INSERT INTO `game_status` VALUES ('not active',NULL,NULL,NULL,'not set','2021-12-8 23:14:06');


DROP TABLE IF EXISTS `pieces`;
CREATE TABLE `pieces` (
  `piece_id` int(12) NOT NULL AUTO_INCREMENT,
  `is_black` tinyint(1) NOT NULL,
  `is_square` tinyint(1) NOT NULL,
  `is_tall` tinyint(1) NOT NULL,
  `is_solid` tinyint(1) NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`piece_id`)
)

INSERT INTO `pieces` VALUES (1,1,0,0,1,1),
                            (2,1,0,0,0,1),
                            (3,1,0,1,1,1),
                            (4,1,0,1,0,1),
                            (5,1,1,0,1,1),
                            (6,1,1,0,0,1),
                            (7,1,1,1,1,1),
                            (8,1,1,1,0,1),
                            (9,0,0,0,1,1),
                            (10,0,0,0,0,1),
                            (11,0,0,1,1,1),
                            (12,0,0,1,0,1),
                            (13,0,1,0,1,1),
                            (14,0,1,0,0,1),
                            (15,0,1,1,1,1),
                            (16,0,1,1,0,1);



DROP TABLE IF EXISTS `pieces_available`;
CREATE TABLE `pieces_available` (
  `piece_id` int(12) NOT NULL AUTO_INCREMENT,
  `is_black` tinyint(1) NOT NULL,
  `is_square` tinyint(1) NOT NULL,
  `is_tall` tinyint(1) NOT NULL,
  `is_solid` tinyint(1) NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`piece_id`)
)

INSERT INTO `pieces_available` VALUES   (1,1,0,0,1,1),
                                        (2,1,0,0,0,1),
                                        (3,1,0,1,1,1),
                                        (4,1,0,1,0,1),
                                        (5,1,1,0,1,1),
                                        (6,1,1,0,0,1),
                                        (7,1,1,1,1,1),
                                        (8,1,1,1,0,1),
                                        (9,0,0,0,1,1),
                                        (10,0,0,0,0,1),
                                        (11,0,0,1,1,1),
                                        (12,0,0,1,0,1),
                                        (13,0,1,0,1,1),
                                        (14,0,1,0,0,1),
                                        (15,0,1,1,1,1),
                                        (16,0,1,1,0,1);


DROP TABLE IF EXISTS `players`;
CREATE TABLE `players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `role` varchar(5) DEFAULT NULL,
  `last_action` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`player_id`)
)

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `clean_board`()
BEGIN
REPLACE INTO board SELECT * FROM board_empty;
END $$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `clean_game_status`()
BEGIN
update `game_status` set `status`='not active' ,`player_turn`=null ,`current_piece`=null ,`result`=null,`win_comb`='not set';
END $$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `place_piece`( X1 TINYINT(1), Y1 TINYINT(1),  piece_id INT)
BEGIN
UPDATE board   
SET piece=pieceid WHERE x=X1 AND y=Y1;
UPDATE pieces 
SET available=false WHERE piece_id=pieceid;
END $$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_pieces`()
BEGIN
REPLACE INTO pieces SELECT * FROM pieces_available;
END $$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_players`()
BEGIN
DELETE FROM `players`;
END $$
DELIMITER ;