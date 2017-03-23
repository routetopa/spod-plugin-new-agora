<?php

OW::getRouter()->addRoute(new OW_Route('spodagora.home', 'agora/:agora_id', "SPODAGORA_CTRL_Agora", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodagora.main', 'agora', "SPODAGORA_CTRL_AgoraMain", 'index'));

OW::getRouter()->addRoute(new OW_Route('spodagora.importer', 'agoraImporter/:roomId', "SPODAGORA_CTRL_Importer", 'index'));

// TEMP
OW::getRouter()->addRoute(new OW_Route('spodagora.old.agora', 'oldAgora', "SPODAGORA_CTRL_AgoraMainOld", 'index'));

//ADMIN
OW::getRouter()->addRoute(new OW_Route('agora-settings', '/agora/settings', 'SPODAGORA_CTRL_Admin', 'settings'));