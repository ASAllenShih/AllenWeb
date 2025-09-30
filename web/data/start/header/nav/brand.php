<?php
require_once __DIR__ . '/../../../../../../main.php';

use Allen\Basic\Util\Language;
use Allen\Basic\Util\Config;
?>
<h1><a href="<?=
				Config::Get('web.start.site_home', '/')
				?><?=
		Language::Get() === 'zh-Hant-TW'
			? ''
			: '?lang=' . Language::Get()
		?>"><img class="round" width="24px" height="24px" src="<?=
																		Config::Get('web.start.web_logo', 'https://cdn.asallenshih.tw/image/Allen.png')
																		?>" alt="網站標識圖片"><?=
																							Config::Get('web.start.site_name', 'AS_Allen_Shih')
																							?></a></h1>