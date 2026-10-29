<?php

namespace Allen\Function\Cloudflare;

use Allen\Basic\Util\Request;
use Allen\Basic\Util\Config;
use Allen\Basic\Util\Server;
use Allen\Basic\Element\Script;
use Allen\Basic\Element\Div;

class Turnstile
{
	public static function Available(): bool
	{
		return is_string(self::SiteKey(required: false)) && is_string(self::SecretKey(required: false));
	}
	public static function SiteKey(bool $required = true): string|null
	{
		return Config::Get('function.cloudflare.turnstile.site_key') ?? ($required ? throw new \Exception('Cloudflare Turnstile site key is not set.') : null);
	}
	public static function SecretKey(bool $required = true): string|null
	{
		return Config::Get('function.cloudflare.turnstile.secret_key') ?? ($required ? throw new \Exception('Cloudflare Turnstile secret key is not set.') : null);
	}
	public static function Validate(string $token, bool|string $remoteIp = false, ?string $secretKey = null): bool
	{
		if (empty($token)) {
			return false;
		}
		$secretKey = $secretKey ?? self::SecretKey();
		$data = [
			'secret' => $secretKey,
			'response' => $token,
		];
		if ($remoteIp === true) {
			$remoteIp = Server::GetIP() ?? false;
		}
		if (is_string($remoteIp)) {
			$data['remoteip'] = $remoteIp;
		}
		$request = new Request(
			url: 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			data: http_build_query($data),
			header: [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		);
		$response = $request->POST();
		if ($response['code'] === 200 && $response['response']['success'] ?? false) {
			return true;
		}
		return false;
	}
	public static function ScriptUrl(bool $explicit = false): string
	{
		return 'https://challenges.cloudflare.com/turnstile/v0/api.js' . ($explicit ? '?render=explicit' : '');
	}
	public static function ScriptElement(bool $explicit = false): Script
	{
		return new Script(
			src: self::ScriptUrl($explicit),
			defer: true,
		);
	}
	public static function ImplicitElement(
		?string $siteKey = null,
		?string $theme = 'auto',
		?string $size = 'normal',
		?string $callback = null,
		?string $errorCallback = null,
		?string $expiredCallback = null,
	): Div {
		return new Div(
			attribute: array_filter([
				'data-sitekey' => $siteKey ?? self::SiteKey(required: false),
				'data-theme' => $theme,
				'data-size' => $size,
				'data-callback' => $callback,
				'data-error-callback' => $errorCallback,
				'data-expired-callback' => $expiredCallback,
			], fn($value) => !is_null($value)),
			class: [
				'cf-turnstile',
			],
		);
	}
}
