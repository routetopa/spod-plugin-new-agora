<?php

OW::getRouter()->addRoute(new OW_Route('spodagora.home', 'agora/:agora_id', "SPODAGORA_CTRL_Agora", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodagora.main', 'agoraMain', "SPODAGORA_CTRL_AgoraMain", 'index'));