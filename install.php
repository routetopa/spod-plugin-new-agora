<?php

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_agora_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerId` int(11) NOT NULL,
  `subject` text,
  `body` text,
  `views` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `opendata` int(11) DEFAULT 0,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `post` varchar(512),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_agora_room_suggestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agoraRoomId` int(11) NOT NULL,
  `dataset` text,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_agora_room_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `comment` text,
  `level` TINYINT,
  `sentiment` TINYINT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;';

OW::getDbo()->query($sql);