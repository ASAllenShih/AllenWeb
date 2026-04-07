<?php

namespace Allen\Basic\Util;

use Allen\Basic\API;

class Language
{
	/**
	 * 全部支援的語言清單
	 */
	public const LANGS = [
		'en-US' => 'English (US)',
		'zh-Hant-TW' => '正體中文(中國)',
		'zh-Hans-TW' => '简化字(中国)',
		'zh-Hant-HK' => '正體中文(港澳地區)',
		'zh-Hans-CN' => '简化字(大陆地區)',
		'ja' => '日本語',
	];
	/**
	 * 取得語言名稱
	 * @param string $lang 語言代碼
	 * @return string|null 語言名稱，若無此語言則回傳 null
	 */
	public static function GetName(string $lang): ?string
	{
		return self::LANGS[$lang] ?? null;
	}
	/**
	 * 目前語言
	 */
	protected static ?string $lang = null;
	/**
	 * 取得目前語言
	 */
	public static function Get(): string
	{
		if (is_null(self::$lang)) {
			$lang_support = self::GetSupport();
			if (!empty($lang_support)) {
				if (isset($_REQUEST['lang'])) {
					$uri = Uri::Parse($_SERVER['REQUEST_URI']);
					if ($_REQUEST['lang'] === 'auto' && $accept_language = Server::GetHeader('Accept-Language')) {
						$accept = [];
						foreach (explode(',', $accept_language) as $part) {
							$subparts = explode(';q=', trim($part));
							$accept[$subparts[0]] = isset($subparts[1]) ? @floatval($subparts[1]) : 1.0;
						}
						arsort($accept);
						foreach (array_keys($accept) as $al) {
							$lang_find = self::GetSelect($lang_support, $al);
							if (!is_null($lang_find) && self::Set($lang_find)) {
								http_response_code(307);
								header('Location: ' . ($lang_find === Config::Get('util.language.default', 'zh-Hant-TW') ? $uri->RemoveQuery('lang') : $uri->AddQuery('lang', $lang_find))->Get());
								die();
							}
						}
					}
					if (!is_string($_REQUEST['lang']) || ($_REQUEST['lang'] === Config::Get('util.language.default', 'zh-Hant-TW') && Server::GetMethod() === 'GET' && !API::IsAPI()) || !self::Set($_REQUEST['lang'])) {
						http_response_code(307);
						header('Location: ' . $uri->RemoveQuery('lang')->Get());
						die();
					}
				} else if (in_array(Config::Get('util.language.default', 'zh-Hant-TW'), $lang_support)) {
					self::Set(Config::Get('util.language.default', 'zh-Hant-TW'));
				} else {
					self::Set($lang_support[array_key_first($lang_support)]);
				}
			} else {
				self::Set(Config::Get('util.language.default', 'zh-Hant-TW'));
			}
		}
		return self::$lang;
	}
	/**
	 * 設定目前語言
	 * @param string $lang 語言代碼
	 * @return bool 是否設定成功
	 */
	public static function Set(string $lang): bool
	{
		if (in_array($lang, self::GetSupport())) {
			self::$lang = $lang;
			return true;
		}
		return false;
	}
	/**
	 * 支援的語言
	 * @var string[]|null
	 */
	protected static ?array $lang_support = null;
	public static function GetSupport(): array
	{
		if (is_null(self::$lang_support)) {
			self::SetSupport();
		}
		return self::$lang_support;
	}
	/**
	 * 設定支援的語言
	 * @param string ...$langs 語言代碼
	 */
	public static function SetSupport(string ...$langs): void
	{
		$lang_support = array_values(array_intersect(array_keys(self::LANGS), $langs));
		if (empty($lang_support)) {
			$config = Config::Get('util.language.default', 'zh-Hant-TW');
			if (array_key_exists($config, self::LANGS)) {
				$lang_support = [
					$config,
				];
			} else {
				$lang_support = [
					array_key_first(self::LANGS),
				];
			}
		}
		self::$lang_support = $lang_support;
	}
	/**
	 * 從可用語言中選擇最適合的語言
	 * @param string[] $langs 可用語言
	 * @param string|null $current 目前語言，預設為 self::Get() 的結果
	 * @param bool $final 若找不到相似語言時是否回傳預設語言，預設為 true
	 * @return string|null 選擇的語言，若語言清單為空則回傳 null
	 */
	public static function GetSelect(array $langs, ?string $current = null, bool $final = true): ?string
	{
		$current ??= self::Get();
		if (in_array($current, $langs)) {
			return $current;
		}
		self::Sort($langs);
		$lang_split = explode('-', $current);
		$lang1 = array_filter($langs, fn($v) => str_starts_with($v, $lang_split[0]));
		if (count($lang1) > 0) {
			if (count($lang_split) > 1) {
				$lang2 = array_filter($lang1, fn($v) => str_starts_with($v, $lang_split[0] . '-' . $lang_split[1]) || str_ends_with($v, '-' . $lang_split[1]));
				if (count($lang2) > 0) {
					return $lang2[array_key_first($lang2)];
				}
			}
			return $lang1[array_key_first($lang1)];
		} else if (!$final) {
			return null;
		}
		$index = array_key_first($langs);
		return $index !== null ? $langs[$index] : null;
	}
	/**
	 * 輸出指定語言的內容
	 * @param array<string, mixed>|string|null $data 語言內容，鍵為語言代碼，值為內容
	 * @param string|null $lang 語言代碼，預設為 self::Get() 的結果
	 * @return mixed|null 指定語言的內容，若無此語言則回傳 null
	 */
	public static function Output(array|string|null $data, ?string $lang = null): mixed
	{
		if (empty($data)) {
			return null;
		} else if (!is_array($data)) {
			return $data;
		}
		$lang ??= self::Get();
		$result_lang = self::GetSelect(array_keys($data), $lang);
		if ($result_lang === null) {
			return null;
		}
		return $data[$result_lang];
	}
	/**
	 * 取得指定語言的年份偏移量
	 * @param string|null $lang 語言代碼，預設為 self::Get() 的結果
	 * @return int 偏移量
	 */
	public static function YearOffset(?string $lang = null): int
	{
		return match ($lang ?? self::Get()) {
			'zh-Hant-TW' => -1911,
			default => 0,
		};
	}
	/**
	 * 檢查語言代碼是否有效
	 * @param string $lang 語言代碼
	 * @return bool 是否有效
	 */
	public static function IsValid(string $lang): bool
	{
		return in_array($lang, array_keys(self::LANGS));
	}
	/**
	 * 對語言清單進行排序
	 * @param string[] $langs 語言代碼陣列
	 */
	public static function Sort(array &$langs): void
	{
		usort($langs, fn($a, $b) => self::Compare($a, $b));
	}
	/**
	 * 對語言清單的鍵進行排序
	 * @param array<string, mixed> $langs 語言代碼陣列
	 */
	public static function SortK(array &$langs): void
	{
		uksort($langs, fn($a, $b) => self::Compare($a, $b));
	}
	protected static function Compare(string $a, string $b): int
	{
		return (array_search($a, self::LANGS) ?: \PHP_INT_MAX) <=> (array_search($b, self::LANGS) ?: \PHP_INT_MAX);
	}
}
