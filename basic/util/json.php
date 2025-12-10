<?php

namespace Allen\Basic\Util;

class Json
{
	public static function Validate(string $json): bool
	{
		return json_validate($json);
	}
	public static function Encode(mixed $data, ?bool $pretty = null): string
	{
		return json_encode(
			value: $data,
			flags: JSON_UNESCAPED_SLASHES
				| JSON_UNESCAPED_UNICODE
				| (($pretty ?? false) ? JSON_PRETTY_PRINT : 0),
		);
	}
	public static function Decode(string $json): mixed
	{
		return json_decode($json, true);
	}
	public static function Output(mixed $data, ?bool $pretty = null, ?bool $etag = null, ?int $last_modified = null): never
	{
		$output = self::Encode($data, $pretty);
		Header::Json(text: $output, etag: $etag, last_modified: $last_modified);
		echo $output;
		exit;
	}
	public static function OutputSuccess(mixed $data, ?bool $pretty = null, ?bool $etag = null, ?int $last_modified = null): never
	{
		$data = [
			'success' => true,
			'data' => $data,
		];
		self::Output(
			data: $data,
			pretty: $pretty,
			etag: $etag,
			last_modified: $last_modified,
		);
	}
	public static function OutputError(?int $code = 500, null|string|array $message = null, ?int $message_id = null, ?bool $pretty = null, ?bool $etag = null, ?int $last_modified = null): never
	{
		if (!is_null($code)) {
			http_response_code($code);
		}
		$data = [
			'success' => false,
			'error' => is_null($message)
				? true
				: $message,
			'code' => $message_id ?? $code,
		];
		self::Output(
			data: $data,
			pretty: $pretty,
			etag: $etag,
			last_modified: $last_modified,
		);
	}
}
