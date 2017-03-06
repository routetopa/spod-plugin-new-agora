<?php

OW::getRouter()->addRoute(new OW_Route('spodagora.home', 'agora/:agora_id', "SPODAGORA_CTRL_Agora", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodagora.main', 'agoraMain', "SPODAGORA_CTRL_AgoraMain", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodagora.importer', 'agoraImporter', "SPODAGORA_CTRL_Importer", 'index'));

// TEMP
OW::getRouter()->addRoute(new OW_Route('spodagora.new.agora', 'newAgora', "SPODAGORA_CTRL_NewAgora", 'index'));