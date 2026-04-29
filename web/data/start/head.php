<?php
use Allen\Basic\Util\Config;
use Allen\Basic\Util\Language;
use Allen\Basic\Util\Uri;
?>

<head>
	<meta charset="UTF-8">
	<title><?= Config::Get('web.start.site_name', 'AS_Allen_Shih') ?> - <?= $title ?? $GLOBALS['title'] ?? '未知標題' ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?= $description ?? $GLOBALS['description'] ?? Config::Get('web.start.site_description', 'Allen服務') ?>">
	<meta name="theme-color" content="<?= Config::Get('web.start.theme_color', '#FFA000') ?>">
	<meta name="og:site_name" content="<?= Config::Get('web.start.site_name', 'AS_Allen_Shih') ?>">
	<meta name="og:title" content="<?= $title ?? $GLOBALS['title'] ?? '未知標題' ?>">
	<meta name="og:description" content="<?= $description ?? $GLOBALS['description'] ?? Config::Get('web.start.site_description', 'Allen服務') ?>">
	<?= implode('', array_map(function ($lang) {
		return '<link rel="alternate" hreflang="' . $lang . '" href="' . (
			$lang === 'zh-Hant-TW'
			? Uri::Parse($_SERVER['REQUEST_URI'])->RemoveQuery('lang')->Get()
			: Uri::Parse($_SERVER['REQUEST_URI'])->AddQuery('lang', $lang)->Get()
		) . '">';
	}, Language::GetSupport())) ?>
	<?php
	require_once __DIR__ . '/head/preconnect.php';
	require_once __DIR__ . '/head/stylesheet.php';
	require_once __DIR__ . '/head/script.php';
	?>
	<meta name="robots" content="<?= Config::Get('web.start.robot.archive', true) ? 'archive' : 'noarchive' ?>, <?= Config::Get('web.start.robot.index', true) ? 'index' : 'noindex' ?>, <?= Config::Get('web.start.robot.follow', true) ? 'follow' : 'nofollow' ?>">
</head>
