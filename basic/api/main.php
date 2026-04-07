<?php

namespace Allen\Basic;

use Allen\Basic\Util\{Json, Language, Server};
use Throwable;

class API
{
	public static function _Execute(
		string $service,
		int $version,
		?string $namespace_prefix = 'Allen\\apis',
		bool $error_handler = true,
	): void {
		self::$is_api = true;
		if ($error_handler) {
			self::_ErrorHandler();
		}
		if (str_contains($service, '.')) {
			self::Error(400, 'Invalid service name, do not include "." character.');
		} else if (str_contains($service, '\\')) {
			self::Error(400, 'Invalid service name, do not include "\\" character.');
		} else if (str_contains($service, '//')) {
			self::Error(400, 'Invalid service name, do not include "//" character.');
		} else if (str_starts_with($service, '/') || str_ends_with($service, '/')) {
			self::Error(400, 'Invalid service name, do not start or end with "/" character.');
		} else if ($version < 1) {
			self::Error(400, 'Invalid version number, please use a positive integer.');
		}
		$namespace = (is_string($namespace_prefix) ? $namespace_prefix . '\\' : '') . str_replace('/', '\\', $service) . '\\v' . $version;
		$class = class_exists($namespace) ? $namespace : ($namespace . '\\main');
		if (!class_exists($class)) {
			self::Error(404, 'Service class does not exist, please check the service name and version number.');
		}
		$method = Server::GetMethod();
		$method_ucf = ucfirst(strtolower($method));
		if (!interface_exists('Allen\\Basic\\API\\Type\\' . $method_ucf)) {
			self::Error(405, 'Request method ' . $method_ucf . ' is not supported.');
		} else if (!is_subclass_of($class, 'Allen\\Basic\\API\\Type\\' . $method_ucf)) {
			self::Error(405, 'Service does not support ' . $method_ucf . ' requests.');
		}
		call_user_func($class . '::' . $method_ucf);
	}
	public static function _Run(
		?string $namespace_prefix = 'Allen\\apis',
		array $service_rewrite = [],
	): void {
		$service = self::InputQuery('service', required: true);
		$version = self::InputQuery('version', required: true);
		if (filter_var($version, FILTER_VALIDATE_INT) === false) {
			self::Error(400, 'Invalid version number, please use a positive integer.');
		}
		$service_rewrite_list = array_filter($service_rewrite, fn($v, $k) => is_string($v) && ($k === $service || str_ends_with($service, $k . '/')), ARRAY_FILTER_USE_BOTH);
		if (!empty($service_rewrite_list)) {
			$service_rewrite_key = array_key_last($service_rewrite_list);
			$service = substr_replace($service, $service_rewrite_list[$service_rewrite_key], strrpos($service, $service_rewrite_key), strlen($service_rewrite_key));
		}
		self::_Execute(
			service: $service,
			version: intval($version),
			namespace_prefix: $namespace_prefix,
		);
	}
	public static function HasQuery(string $query): bool
	{
		return isset($_REQUEST[$query]);
	}
	public static function InputHeader(?string $header = null, bool $required = true): array|string|null
	{
		if (is_null($header)) {
			return Server::GetHeaders();
		}
		$content = Server::GetHeader($header);
		if (is_null($content) && $required) {
			self::Error(
				code: 400,
				message: [
					'en-US' => "Header '$header' is required.",
					'zh-Hant-TW' => "標頭 '$header' 是必需的。",
				],
			);
		}
		return $content;
	}
	public static function InputQuery(?string $query = null, bool $required = true, array|string|null $default = null, ?array $allow = null): array|string|null
	{
		if (is_null($query)) {
			return $_REQUEST;
		} else if (isset($_REQUEST[$query])) {
			$value = $_REQUEST[$query];
			if (is_array($allow) && !in_array($value, $allow)) {
				self::Error(400, [
					'en-US' => "The value of parameter '$query' is not in the allowed range.",
					'zh-Hant-TW' => "參數 '$query' 的值不在允許的範圍內。",
				]);
			}
			return $value;
		} else if ($required) {
			self::Error(
				code: 400,
				message: [
					'en-US' => "Parameter '$query' is required.",
					'zh-Hant-TW' => "參數 '$query' 是必需的。",
				],
			);
		}
		return $default;
	}
	public static function InputData(bool $required = true, bool $json = true): mixed
	{
		$data = file_get_contents('php://input');
		if (empty($data)) {
			if ($required) {
				self::Error(400, '請求的資料格式不得為空。');
			}
			return null;
		} else if ($json) {
			if (!Json::Validate($data)) {
				if ($required) {
					self::Error(400, '請求的資料格式錯誤，應為 JSON 格式。');
				}
				return null;
			}
			return Json::Decode($data);
		}
		return $data;
	}
	public static function InputQueryInt(string $query, bool $required = true, ?int $default = null, ?int $min = null, ?int $max = null): ?int
	{
		$value = self::InputQuery(
			query: $query,
			required: $required,
		);
		if (is_null($value)) {
			return $default;
		} else if (filter_var($value, FILTER_VALIDATE_INT) === false) {
			self::Error(400, [
				'en-US' => "Parameter '$query' must be an integer.",
				'zh-Hant-TW' => "參數 '$query' 必須是一個整數。",
			]);
		}
		$value = intval($value);
		if (!is_null($min) && $value < $min) {
			self::Error(400, [
				'en-US' => "Parameter '$query' must be greater than or equal to '$min'.",
				'zh-Hant-TW' => "參數 '$query' 必須大於或等於 '$min'。",
			]);
		} else if (!is_null($max) && $value > $max) {
			self::Error(400, [
				'en-US' => "Parameter '$query' must be less than or equal to '$max'.",
				'zh-Hant-TW' => "參數 '$query' 必須小於或等於 '$max'。",
			]);
		}
		return $value;
	}
	public static function Output(mixed $data, ?bool $pretty = null, ?bool $etag = null, ?int $last_modified = null): never
	{
		Json::Output(data: $data, pretty: $pretty, etag: $etag, last_modified: $last_modified);
	}
	public static function Success(mixed $data, array $more = [], ?bool $pretty = null, ?bool $etag = null, ?int $last_modified = null): never
	{
		Json::OutputSuccess(
			data: $data,
			more: $more,
			pretty: $pretty,
			etag: $etag,
			last_modified: $last_modified,
		);
	}
	public static function Error(?int $code = 500, null|string|array $message = null, ?int $message_id = null, array $more = [], ?bool $pretty = null, ?bool $etag = null, ?int $last_modified = null): never
	{
		Json::OutputError(
			message: $message,
			code: $code,
			message_id: $message_id,
			more: $more,
			pretty: $pretty,
			etag: $etag,
			last_modified: $last_modified,
		);
	}
	protected static bool $is_api = false;
	public static function IsAPI(): bool
	{
		return self::$is_api;
	}
	public static function Lang(array|string|null $data): array|string|null
	{
		if (is_null($data) || is_string($data)) {
			return $data;
		}
		$lang = self::InputQuery('lang', required: false, allow: array_keys(Language::LANGS));
		if (empty($lang)) {
			return $data;
		} else if (is_string($lang)) {
			return Language::Output($data, lang: $lang);
		} else if (is_array($lang)) {
			return array_filter(
				$data,
				fn($k) => in_array($k, $lang),
				ARRAY_FILTER_USE_KEY,
			);
		}
		return $data;
	}
	public static function RunAPIs(): void
	{
		self::_Run(
			namespace_prefix: 'Allen\\apis',
			service_rewrite: [
				'allen/news' => 'news',
			],
		);
	}
	protected const ERROR_MESSAGE_500 = [
		'en-US' => 'Server Error. Please try again later.',
		'zh-Hant-TW' => '伺服器錯誤，請稍後再試。',
	];
	protected static function _ErrorHandler(): void
	{
		set_error_handler(function ($errno, $errstr, $errfile, $errline) {
			if (!(error_reporting() & $errno)) {
				return false;
			}
			$errtype = match ($errno) {
				E_ERROR => 'Fatal Error',
				E_WARNING => 'Warning',
				E_PARSE => 'Parse Error',
				E_NOTICE => 'Notice',
				E_DEPRECATED => 'Deprecated Notice',
				default => 'Unknown Error',
			};
			if ($errtype === 'Notice') {
				return true;
			}
			self::Error(
				code: 500,
				message: array_map(fn($m) => $m . PHP_EOL . $errtype . ' ' . $errstr . ' (' . $errfile . ':' . $errline . ')', self::ERROR_MESSAGE_500),
			);
		}, E_ALL);
		error_reporting(E_ALL);
		set_exception_handler(function (Throwable $exception) {
			self::Error(
				code: 500,
				message: array_map(fn($m) => $m . PHP_EOL . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')', self::ERROR_MESSAGE_500),
			);
		});
	}
}
