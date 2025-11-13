<?php

use Allen\Basic\Util\{Config, ConfigType};

Config::SetType('web.dynamic_page', ConfigType::Bool);
Config::SetType('web.start.site_name', ConfigType::String);
Config::SetType('web.start.site_description', ConfigType::String);
Config::SetType('web.start.site_home', ConfigType::String);
Config::SetType('web.start.web_logo', ConfigType::String);
Config::SetType('web.start.web_logo_px', ConfigType::Int);
Config::SetType('web.start.theme_color', ConfigType::String);
Config::SetType('web.start.link.preconnect', ConfigType::Array);
Config::SetType('web.start.link.web_style', ConfigType::String);
Config::SetType('web.start.link.stylesheet', ConfigType::Array);
Config::SetType('web.start.link.script', ConfigType::Array);
Config::SetType('web.start.menu.static', ConfigType::Bool);
Config::SetType('web.start.robot.archive', ConfigType::Bool);
Config::SetType('web.start.robot.index', ConfigType::Bool);
Config::SetType('web.start.robot.follow', ConfigType::Bool);
Config::SetType('web.end.author', ConfigType::String);
Config::SetType('web.end.footer', ConfigType::Array);
Config::SetType('web.end.language_choose', ConfigType::Bool);
Config::Set('web.start.link.preconnect', [
	'https://cdn.asallenshih.tw',
	'https://fonts.googleapis.com',
	['https://fonts.gstatic.com', 'crossorigin'],
]);
Config::Set('web.start.link.stylesheet', [
	'https://fonts.googleapis.com/css2?family=Noto+Sans&family=Noto+Sans+TC&display=swap',
	'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block',
	'https://cdn.jsdelivr.net/npm/aos/dist/aos.min.css',
]);
Config::Set('web.start.link.script', [
	['https://cdn.asallenshih.tw/js/aos.js', 'id="aosImport"'],
]);
