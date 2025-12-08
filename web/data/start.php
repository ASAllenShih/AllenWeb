<?php

use Allen\Basic\Util\{Config, Language};

Config::Init();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_DynPage']) && Config::Get('web.dynamic_page', false)) {
	$dynamic_page = [
		'title' => $title ?? '未知標題',
		'site_name' => Config::Get('web.start.site_name', 'AS_Allen_Shih'),
	];
	ob_start();
}
?>
<!DOCTYPE html>
<html lang="<?= Language::Get() ?>">
<?php
require_once __DIR__ . '/start/head.php';
?>
<body>
	<?php
	if ($header ?? true) require_once __DIR__ . '/start/header.php';
	?>
	<main>
		<?php
		if (isset($dynamic_page)) {
			ob_clean();
		}
		?>