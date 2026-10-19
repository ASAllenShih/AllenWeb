<?php

namespace Allen\Basic\Util;

class Server
{
	public static function GetMethod(): string
	{
		return $_SERVER['REQUEST_METHOD'] ?? 'GET';
	}
	public static function GetHeaders(): array
	{
		$headers = getallheaders();
		return array_change_key_case(is_array($headers) ? $headers : [], \CASE_LOWER);
	}
	public static function GetHeader(string $name): ?string
	{
		$headers = self::GetHeaders();
		return $headers[strtolower($name)] ?? null;
	}
	public static function GetDomain(?string $domain = null, bool $idn = true): ?string
	{
		$domain ??= $_SERVER['HTTP_HOST'] ?? null;
		if (is_null($domain)) return null;
		return $idn ? idn_to_utf8($domain) : $domain;
	}
	public static function GetDomainPath(?string $domain = null, bool $idn = true): ?array
	{
		$domain = self::GetDomain($domain, $idn);
		if (is_null($domain)) return null;
		return array_reverse(explode('.', $domain));
	}
	public static function GetIP(): ?string
	{
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
		if (is_null($ip)) return null;
		if (str_contains($ip, ',')) {
			$ip = explode(',', $ip)[0];
		}
		return trim($ip);
	}
}
