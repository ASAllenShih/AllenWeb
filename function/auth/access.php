<?php

namespace Allen\Function\Auth;

use Allen\Web;
use Allen\Basic\{Path, Util\Config};
use Firebase\JWT\{JWT, Key};
use Exception;
use InvalidArgumentException;

class Access
{
	static public function TeamName(): string
	{
		$data = Config::Get('function.auth.access.team');
		if (!is_string($data)) {
			throw new InvalidArgumentException('Config function.auth.access.team is not string');
		}
		return $data;
	}
	static protected function Aud(): string
	{
		$data = Config::Get('function.auth.access.aud');
		if (!is_string($data)) {
			throw new InvalidArgumentException('Config function.auth.access.aud is not string');
		}
		return $data;
	}
	static protected function JWTFile(): string
	{
		return Path::Cache('cf_jwt.json');
	}
	static private array $auth_result = [];
	static protected function GetJWT(): string
	{
		$jwt = $_SERVER['HTTP_CF_ACCESS_JWT_ASSERTION'] ?? null;
		if (empty($jwt)) {
			throw new Exception('找不到JWT');
		}
		return $jwt;
	}
	static protected function GetJWTArray(?string $jwt = null): array
	{
		if ($jwt === null) {
			$jwt = self::GetJWT();
		}
		$jwts = explode('.', $jwt, 3);
		if (count($jwts) !== 3) {
			throw new Exception('JWT格式錯誤');
		}
		return [
			'header' => $jwts[0],
			'payload' => $jwts[1],
			'signature' => $jwts[2],
		];
	}
	static protected function GetJWTData(?string $jwt_data): array
	{
		if ($jwt_data === null) {
			throw new Exception('JWT格式錯誤');
		}
		$base64 = @base64_decode($jwt_data);
		if ($base64 === false || !json_validate($base64)) {
			throw new Exception('JWT格式錯誤');
		}
		return json_decode($base64, true);
	}
	static protected function GetCert_String_Local(): ?string
	{
		if (is_file(self::JWTFile()) && filemtime(self::JWTFile()) > time() - 86400 * 7) {
			return @file_get_contents(self::JWTFile());
		}
		return null;
	}
	static protected function GetCert_String_Cloudflare(): ?string
	{
		$jwt_certs_url = 'https://' . self::TeamName() . '.cloudflareaccess.com/cdn-cgi/access/certs';
		$response = @file_get_contents($jwt_certs_url);
		if ($response === false) {
			return null;
		}
		if (!is_dir(dirname(self::JWTFile()))) @mkdir(dirname(self::JWTFile()), 0755, true);
		@file_put_contents(self::JWTFile(), $response);
		return $response;
	}
	static protected function GetCert_String(bool $force = false): string
	{
		$jwt_certs = self::GetCert_String_Local();
		if ($jwt_certs === null || $force) {
			$jwt_certs = self::GetCert_String_Cloudflare();
		}
		if ($jwt_certs === null) {
			throw new Exception('找不到JWT憑證');
		}
		return $jwt_certs;
	}
	static protected function GetCert(bool $force = false): ?array
	{
		$jwt_certs = self::GetCert_String($force);
		if (!json_validate($jwt_certs)) {
			throw new Exception('找不到JWT憑證');
		}
		return json_decode($jwt_certs, true);
	}
	static protected function GetCertPublicCerts(?array $jwt_cert = null, bool $force = false): ?array
	{
		if ($jwt_cert === null) {
			$jwt_cert = self::GetCert($force);
		}
		$jwt_cert_public_cert = $jwt_cert['public_certs'] ?? null;
		if ($jwt_cert_public_cert === null) {
			throw new Exception('找不到JWT憑證');
		}
		return $jwt_cert_public_cert;
	}
	static protected function GetCertPublicCert(string $kid, ?array $jwt_cert_public_certs = null, bool $is_force = false): array
	{
		if ($jwt_cert_public_certs === null) {
			$jwt_cert_public_certs = self::GetCertPublicCerts(null, false);
		}
		$jwt_cert_public_cert = array_find($jwt_cert_public_certs, function ($cert) use ($kid) {
			return ($cert['kid'] ?? null) === $kid;
		});
		if ($jwt_cert_public_cert === null) {
			if (!$is_force) {
				$jwt_cert_public_cert = self::GetCertPublicCert($kid, null, true);
			} else {
				throw new Exception('找不到JWT憑證');
			}
		}
		return $jwt_cert_public_cert;
	}
	public static function Auth(): array|null
	{
		if (empty(self::$auth_result)) {
			try {
				$jwt = self::GetJWT();
				$jwt_header_kid = self::GetJWTData(self::GetJWTArray($jwt)['header'])['kid'];
				$cert = self::GetCertPublicCert($jwt_header_kid)['cert'];
				$decode = JWT::decode($jwt, new Key($cert, 'RS256'));
				$decode = (array) $decode;
				if (isset(self::$aud) && is_string(self::Aud()) && !in_array(self::Aud(), $decode['aud'] ?? [])) {
					throw new Exception();
				}
			} catch (Exception $e) {
				header('Refresh: 3');
				global $title;
				$title = '確認登入狀態';
				Web::Start();
				echo '<h2>登入狀態確認中，請稍候</h2>';
				Web::End();
				exit;
			}
			self::$auth_result = $decode;
		}
		return self::$auth_result;
	}
	public static function GetEmail(): ?string
	{
		$auth = self::Auth();
		return $auth['email'] ?? null;
	}
}
