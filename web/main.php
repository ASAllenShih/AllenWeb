<?php

namespace Allen;

use Allen\Basic\Util\{Config, Header};

class Web
{
	/**
	 * 啟動網頁
	 * @param ?string $title 標題
	 * @param ?string $description 描述
	 * @param bool $header 是否啟用開頭HTML
	 * @param bool $header_nav 是否啟用導覽列
	 * @param bool $header_title 是否啟用標題
	 * @param bool $etag 是否啟用 ETag 功能
	 * @param bool $cache 是否啟用快取標頭
	 * @param ?bool $cache_public 是否為公用快取
	 * @param ?bool $cache_no_cache 是否禁止快取
	 * @param ?bool $cache_no_store 是否禁止儲存快取
	 * @param ?int $cache_max_age 最大快取時間(秒)
	 * @param ?bool $cache_must_revalidate 是否要求重新驗證
	 * @return void
	 */
	public static function Start(
		?string $title = null,
		?string $description = null,
		bool $header = true,
		bool $header_nav = true,
		bool $header_title = true,
		bool $etag = true,
		bool $cache = true,
		?bool $cache_public = null,
		?bool $cache_no_cache = null,
		?bool $cache_no_store = null,
		?int $cache_max_age = null,
		?bool $cache_must_revalidate = null,
		string $id = '',
	): void {
		if (self::$id !== null) return;
		self::$id = $id;
		if ($etag === true) ob_start(function (string $content) {
			Header::ContentLength($content);
			Header::ETag($content);
			return $content;
		});
		if ($cache === true) Header::CacheControl(
			public: $cache_public,
			no_cache: $cache_no_cache,
			no_store: $cache_no_store,
			max_age: $cache_max_age,
			must_revalidate: $cache_must_revalidate,
		);
		require_once __DIR__ . '/data/start.php';
	}
	/**
	 * 結束網頁
	 * @param ?string $script 腳本HTML
	 * @param bool $footer 是否啟用結尾HTML
	 * @return void
	 */
	public static function End(
		?string $script = null,
		bool $footer = true,
		string $id = '',
	): void {
		if (self::$id === null || self::$id !== $id) return;
		require_once __DIR__ . '/data/end.php';
	}
	public static function Config(): void
	{
		Config::Init();
	}
	protected static ?string $id = null;
}
