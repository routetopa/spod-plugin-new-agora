<?php

$sql = 'DROP TABLE `' . OW_DB_PREFIX . 'spod_agora_room`;
        DROP TABLE `' . OW_DB_PREFIX . 'spod_agora_room_suggestion`;
        DROP TABLE `' . OW_DB_PREFIX . 'spod_agora_room_comment`;
        DROP TABLE `' . OW_DB_PREFIX . 'spod_agora_room_user_access`;';

OW::getDbo()->query($sql);