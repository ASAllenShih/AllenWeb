<?php

namespace Allen\Basic\Util;

use CurlHandle;

class Request
{
	/**
	 * 請求
	 * @param string|null $url 請求的 URL
	 * @param string|null $data 請求的資料
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent，預設為 Config::Get('util.request.ua') 或不設定
	 */
	public function __construct(
		protected ?string $url = null,
		protected ?string $data = null,
		protected array $header = [],
		protected ?string $ua = null,
	) {}
	/**
	 * 取得請求 URL
	 * @return string|null
	 */
	public function UrlGet(): ?string
	{
		return $this->url ?? null;
	}
	/**
	 * 設定請求 URL
	 * @param string $url 請求的 URL
	 */
	public function UrlSet(string $url): self
	{
		$this->url = $url;
		return $this;
	}
	/**
	 * 取得請求資料
	 */
	public function DataGet(): ?string
	{
		return $this->data;
	}
	/**
	 * 設定請求資料
	 * @param string|null $data 請求的資料
	 */
	public function DataSet(?string $data): self
	{
		$this->data = $data;
		return $this;
	}
	/**
	 * 取得 Header
	 * @return array<string, string>
	 */
	public function HeaderGet(): array
	{
		return array_filter($this->header);
	}
	/**
	 * 新增 Header
	 * @param string $key Header 的 Key
	 * @param string|null $value Header 的 Value
	 */
	public function HeaderAdd(string $key, ?string $value): self
	{
		$this->header[$key] = $value;
		return $this;
	}
	/**
	 * 設定 Header
	 * @param array<string, string|null> $header
	 */
	public function HeaderSet(array $header): self
	{
		$this->header = $header;
		return $this;
	}
	/**
	 * 取得 User-Agent
	 * @return string|null
	 */
	public function UaGet(): ?string
	{
		if ($this->ua !== null) {
			return $this->ua;
		}
		return Config::Get('util.request.ua');
	}
	/**
	 * 設定 User-Agent
	 * @param string|null $ua User-Agent
	 */
	public function UaSet(?string $ua): self
	{
		$this->ua = $ua;
		return $this;
	}
	/**
	 * 執行 GET 請求
	 * @param string|null $url 請求的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function GET(
		?string $url = null,
		array $header = [],
		?string $ua = null,
	) {
		return $this->_CurlEnd($this->_CurlGET(
			url: $url,
			header: $header,
			ua: $ua,
		));
	}
	/**
	 * 執行 POST 請求
	 * @param string|null $data 上傳的資料
	 * @param string|null $url 請求的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function POST(
		?string $data = null,
		?string $url = null,
		array $header = [],
		?string $ua = null,
	) {
		return $this->_CurlEnd($this->_CurlPOST(
			data: $data,
			url: $url,
			header: $header,
			ua: $ua,
		));
	}
	/**
	 * 執行 PUT 請求
	 * @param string|null $data 上傳的資料
	 * @param string|null $url 請求的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function PUT(
		?string $data = null,
		?string $url = null,
		array $header = [],
		?string $ua = null,
	) {
		return $this->_CurlEnd($this->_CurlPUT(
			data: $data,
			url: $url,
			header: $header,
			ua: $ua,
		));
	}
	/**
	 * 初始化 GET 請求
	 * @param string|null $url 請求的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function _CurlGET(
		?string $url = null,
		array $header = [],
		?string $ua = null,
	): CurlHandle {
		$ch = $this->_CurlStart(
			method: 'GET',
			url: $url,
			header: $header,
			ua: $ua,
		);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		return $ch;
	}
	/**
	 * 初始化 POST 請求
	 * @param string|null $data 上傳的資料
	 * @param string|null $url 請求的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function _CurlPOST(
		?string $data = null,
		?string $url = null,
		array $header = [],
		?string $ua = null,
	): CurlHandle {
		$ch = $this->_CurlStart(
			method: 'POST',
			data: $data,
			url: $url,
			header: $header,
			ua: $ua,
		);
		return $ch;
	}
	/**
	 * 初始化 PUT 請求
	 * @param string|null $data 上傳的資料
	 * @param string|null $url 請求的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function _CurlPUT(
		?string $data = null,
		?string $url = null,
		array $header = [],
		?string $ua = null,
	): CurlHandle {
		$ch = $this->_CurlStart(
			method: 'PUT',
			data: $data,
			url: $url,
			header: $header,
			ua: $ua,
		);
		return $ch;
	}
	/**
	 * 初始化 Curl 請求
	 * @param string|null $method 請求方法，若為 null 則不設定
	 * @param string|null $data 上傳的資料
	 * @param string|null $url 請求的 URL，若為 null 則使用物件內的 URL
	 * @param array<string, string|null> $header 請求的 Header
	 * @param string|null $ua 使用的 User-Agent
	 */
	public function _CurlStart(
		?string $method = null,
		?string $data = null,
		?string $url = null,
		array $header = [],
		?string $ua = null,
	): CurlHandle {
		$header = array_filter(array_merge($this->HeaderGet(), $header));
		$header = array_values(array_map(function ($key, $value) {
			return $key . ': ' . $value;
		}, array_keys($header), $header));
		$data ??= $this->DataGet();
		$url ??= $this->UrlGet();
		$ua ??= $this->UaGet();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		if (!is_null($method)) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if (!is_null($ua)) curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		return $ch;
	}
	/**
	 * 結束 Curl 請求
	 * @param CurlHandle $ch
	 * @return array{code: int, response: mixed, header: array<string, string[]>}
	 */
	public function _CurlEnd(CurlHandle $ch)
	{
		$headers = [];
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$headers) {
			$len = strlen($header);
			$header_array = explode(':', $header, 2);
			if (count($header_array) < 2) {
				return $len;
			}
			$name = strtolower(trim($header_array[0]));
			$value = trim($header_array[1]);
			if (!array_key_exists($name, $headers)) {
				$headers[$name] = [];
			}
			$headers[$name][] = $value;
			return $len;
		});
		return self::_CurlReturn(
			ch: $ch,
			headers: $headers,
		);
	}
	protected static function _CurlReturn(CurlHandle $ch, array &$headers, null|string|bool $response = null)
	{
		$response ??= curl_exec($ch);
		$error = curl_error($ch);
		if (!empty($error) && $response === false) {
			$code = 0;
			$response = [
				'error' => $error,
			];
		} else {
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (json_validate($response)) {
				$response = json_decode($response, true);
			}
		}
		return [
			'code' => $code,
			'response' => $response,
			'header' => $headers,
		];
	}
	/**
	 * 同時執行多個請求
	 * @param array<string|int, Request> $get GET 請求陣列
	 * @param array<string|int, Request> $post POST 請求陣列
	 * @param array<string|int, Request> $put PUT 請求陣列
	 */
	public static function Multi(
		array $get = [],
		array $post = [],
		array $put = [],
	) {
		$get = array_map(fn($v) => $v->_CurlGET(), array_filter($get, fn($v) => $v instanceof Request));
		$post = array_map(fn($v) => $v->_CurlPOST(), array_filter($post, fn($v) => $v instanceof Request));
		$put = array_map(fn($v) => $v->_CurlPUT(), array_filter($put, fn($v) => $v instanceof Request));
		$chs = array_merge($get, $post, $put);
		$mh = curl_multi_init();
		$headers = [];
		foreach ($chs as $key => $ch) {
			$headers[$key] = [];
			curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$headers, $key) {
				$len = strlen($header);
				$pos = strpos($header, ':');
				if ($pos === false) {
					return $len;
				}
				$name = strtolower(substr($header, 0, $pos));
				$value = trim(substr($header, $pos + 1));
				if (!array_key_exists($name, $headers[$key])) {
					$headers[$key][$name] = [];
				}
				$headers[$key][$name][] = $value;
				return $len;
			});
			curl_multi_add_handle($mh, $ch);
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) !== -1) {
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		$results = [];
		foreach ($chs as $key => $ch) {
			$results[$key] = self::_CurlReturn(
				ch: $ch,
				headers: $headers[$key],
				response: curl_multi_getcontent($ch) ?? false,
			);
			curl_multi_remove_handle($mh, $ch);
		}
		curl_multi_close($mh);
		return $results;
	}
}
