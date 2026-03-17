<?php
use Allen\Basic\Util\Config;
use Allen\Basic\Util\Server;

?>
<footer>
	<?php
	require_once __DIR__ . '/footer/year.php';
	require_once __DIR__ . '/footer/info.php';
	?>
	<?= implode(
		'',
		array_map(
			fn($footer) => '<div>' . $footer . '</div>',
			Config::Get('web.end.footer', []),
		),
	) ?>
	<?php
	if (Config::Get('web.end.language_choose', true) && Server::GetMethod() === 'GET') {
		require_once __DIR__ . '/footer/language.php';
	}
	?>
</footer>