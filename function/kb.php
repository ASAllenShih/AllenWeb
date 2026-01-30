<?php

namespace Allen\Function;

use Exception;
use Allen\Web;
use Allen\Basic\Util\{Language, Uri};
use Allen\Basic\Element\Button\ButtonLink;

/**
 * 知識庫
 */
class Kb
{
	public readonly ?string $id;
	public function __construct(
		public readonly string $name,
		public readonly string $base,
	) {
		$this->id = $_REQUEST['id'] ?? null;
		if (!is_null($this->id)) {
			if (str_starts_with($this->id, '/') || str_ends_with($this->id, '/') || str_contains($this->id, '..')) {
				http_response_code(400);
				exit;
			}
		}
	}
	/**
	 * 取得知識庫檔案路徑
	 */
	private function File(string $kb): ?string
	{
		if (is_file($this->base . $kb . '.php')) {
			return $this->base . $kb . '.php';
		} else if (is_file($this->base . $kb . '/index.php')) {
			return $this->base . $kb . '/index.php';
		} else {
			return null;
		}
	}
	/**
	 * 取得知識庫資訊
	 * @return array{title: ?string, description: ?string, content: ?string, error?: string}|null
	 */
	private function Info(string $kb): ?array
	{
		$kb_file = $this->File($kb);
		if (is_null($kb_file)) {
			return null;
		}
		$output = [];
		ob_start();
		try {
			require $kb_file;
		} catch (Exception $e) {
			$output['error'] = $e->getMessage();
		}
		$output['title'] = $title ?? null;
		$output['description'] = $description ?? null;
		$output['content'] = ob_get_clean() ?? null;
		return $output;
	}
	/**
	 * 列出知識庫目錄下的所有檔案
	 * @return string[]
	 */
	private function ListDir(string $dir_name = '', bool $recursive = false): array
	{
		$dir = rtrim($this->base, '/') . '/' . ltrim($dir_name, '/');
		if (!is_dir($dir)) {
			return [];
		}
		$files = scandir($dir);
		$output = array_map(function ($file) {
			return str_replace('.php', '', $file);
		}, array_values(array_filter($files, function ($file) use ($dir) {
			return !in_array($file, ['..', '.', 'index.php']) && !str_starts_with($file, '_') && (((is_file($dir . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php')) || is_file($dir . '/' . $file . '/index.php'));
		})));
		if ($recursive) {
			$output = array_merge($output, ...array_map(function ($file) use ($dir_name) {
				return array_map(function ($file_name) use ($file) {
					return $file . '/' . $file_name;
				}, $this->ListDir($dir_name . '/' . $file, true));
			}, $output));
		}
		return $output;
	}
	/**
	 * 顯示知識庫內容
	 */
	public function Show()
	{
		global $title, $description;
		$title = trim($this->name . Language::Output([
			'en-US' => ' Knowledge Base',
			'zh-Hant-TW' => '知識庫',
			'zh-Hans-TW' => '知识库',
		]));
		if (!is_null($this->id)) {
			$info = $this->Info($this->id);
			if (is_null($info)) {
				http_response_code(404);
				$description = Language::Output([
					'en-US' => 'The requested knowledge base entry does not exist.',
					'zh-Hant-TW' => '找不到指定的知識庫條目。',
					'zh-Hans-TW' => '找不到指定的知识库条目。',
				]);
				Web::Start();
?>
				<h2><?= $description ?></h2>
			<?php
				Web::End();
				return;
			} else if (isset($info['error']) && !is_string($info['error'])) {
				http_response_code(500);
				$description = Language::Output([
					'en-US' => 'An error occurred while loading the knowledge base entry: ' . $info['error'],
					'zh-Hant-TW' => '載入知識庫條目時發生錯誤：' . $info['error'],
					'zh-Hans-TW' => '载入知识库条目时发生错误：' . $info['error'],
				]);
				Web::Start();
			?>
				<h2><?= $description ?></h2>
			<?php
				Web::End();
				return;
			}
			if (!is_null($info['title'])) $title .= ' - ' . $info['title'];
			$description = $info['description'] ?? Language::Output([
				'en-US' => 'No description available.',
				'zh-Hant-TW' => '無相關描述',
				'zh-Hans-TW' => '无相关描述',
			]);
		}
		Web::Start();
		if (is_null($this->id)) {
			?>
			<p><?= Language::Output([
					'en-US' => 'This is the ',
					'zh-Hant-TW' => '這裡是',
					'zh-Hans-TW' => '这里是',
				]) . $this->name . Language::Output([
					'en-US' => ' Knowledge Base, where you can find frequently asked questions and helpful information about ',
					'zh-Hant-TW' => '知識庫，您可以在這裡找到有關',
					'zh-Hans-TW' => '知识库，您可以在这里找到有关',
				]) . $this->name . Language::Output([
					'en-US' => '.',
					'zh-Hant-TW' => '常見幫助資訊。',
					'zh-Hans-TW' => '常见帮助资讯。',
				]) ?></p>
			<p><?= Language::Output([
					'en-US' => 'If you have any questions that are not addressed here, please contact our support team.',
					'zh-Hant-TW' => '如果您有任何無法解決的問題，請聯絡我們的客服。',
					'zh-Hans-TW' => '如果您有任何无法解决的问题，请联络我们的客服。',
				]) ?></p>
			<div class="card flex">
				<?php
				foreach ($this->ListDir() as $kb) {
					$kb_info = $this->Info($kb);
					if (is_null($kb_info)) continue;
				?>
					<div class="card">
						<h3><?= $kb_info['title'] ?? '未知' ?></h3>
						<p><?= nl2br(htmlspecialchars($kb_info['description'] ?? '無相關描述')) ?></p>
						<?php echo new ButtonLink(content: Language::Output([
							'en-US' => 'Learn More',
							'zh-Hant-TW' => '了解更多',
							'zh-Hans-TW' => '了解更多',
						]), href: Uri::Link('?id=' . $kb, lang: true, querySlash: true)); ?>
					</div>
				<?php
				}
				?>
			</div>
		<?php
		} else {
		?>
			<?= $info['content'] ?? '' ?>
			<?php
			if (!empty(trim($info['content'] ?? ''))) { ?>
				<hr>
			<?php
			}
			$kb_list = $this->ListDir($this->id);
			if (!empty($kb_list)) {
			?>
				<h3><?= Language::Output([
						'en-US' => 'Related Articles',
						'zh-Hant-TW' => '相關文章',
						'zh-Hans-TW' => '相关文章',
					]) ?></h3>
				<div class="card flex">
					<?php
					foreach ($kb_list as $kb) {
						$info = $this->Info($this->id . '/' . $kb);
						if (is_null($info)) continue;
					?>
						<div class="card">
							<h4><?= $info['title'] ?? '未知' ?></h4>
							<p><?= nl2br(htmlspecialchars($info['description'] ?? '無相關描述')) ?></p>
							<?= new ButtonLink(content: Language::Output([
								'en-US' => 'Learn More',
								'zh-Hant-TW' => '了解更多',
								'zh-Hans-TW' => '了解更多',
							]), href: Uri::Link('?id=' . $this->id . '/' . $kb, lang: true, querySlash: true)) ?>
						</div>
					<?php
					}
					?>
				</div>
			<?php
			}
			?>
			<?= new ButtonLink(content: Language::Output([
				'en-US' => 'Back to Knowledge Base Home',
				'zh-Hant-TW' => '回知識庫首頁',
				'zh-Hans-TW' => '回知识库首页',
			]), href: Uri::Link('?', lang: true)) ?>
			<?php if (count(explode('/', $this->id)) > 1) { ?>
				<?= new ButtonLink(content: Language::Output([
					'en-US' => 'Back to Parent',
					'zh-Hant-TW' => '回上層',
					'zh-Hans-TW' => '回上层',
				]), href: Uri::Link('?id=' . implode('/', array_slice(explode('/', $this->id), 0, -1)), lang: true, querySlash: true)) ?>
<?php
			}
		}
		Web::End();
	}
}
