<?php

use Allen\Basic\Util\Json;

if (isset($dynamic_page)) {
	$dynamic_page['content'] = ob_get_clean();
	Json::Output($dynamic_page);
}
?>
		</main>
		<?php
		require_once __DIR__ . '/end/footer.php';
		?>
		<?= $script ?? $GLOBALS['script'] ?? '' ?>
	</body>
</html>