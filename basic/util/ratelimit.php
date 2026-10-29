<?php

namespace Allen\Basic\Util;

class RateLimit
{
	protected static bool $hasClean = false;
	public static function Clean(): void
	{
		if (self::$hasClean) return;
		self::$hasClean = true;
		$db = Db::Get();
		// 先清除資料庫內已過期的時間
		$db->query('DELETE FROM `rate_limit` WHERE `release` < NOW()');
	}
	public static function Clear(?string $id, bool $like = false): void
	{
		$db = Db::Get();
		if (is_null($id)) {
			$db->query('DELETE FROM `rate_limit`');
		} else {
			$stmt = $db->prepare('DELETE FROM `rate_limit` WHERE `id` ' . ($like ? 'LIKE' : '=') . ' ?');
			$stmt->execute([$id]);
		}
	}
	public static function Check(int $limit, int $seconds, string $id): ?int
	{
		self::Clean();
		$db = Db::Get();
		// 取得目前的請求數量
		$stmt = $db->prepare('SELECT COUNT(*) AS `count` FROM `rate_limit` WHERE `id` = ?');
		$stmt->execute([$id]);
		$count = $stmt->fetchColumn();
		if ($count >= $limit) {
			// 超過限制，回傳剩餘時間
			$stmt = $db->prepare('SELECT TIMESTAMPDIFF(SECOND, NOW(), `release`) AS `remaining` FROM `rate_limit` WHERE `id` = ? ORDER BY `release` ASC LIMIT 1');
			$stmt->execute([$id]);
			$remaining = $stmt->fetchColumn();
			return (int)$remaining;
		}
		// 新增一筆新的請求記錄
		$stmt = $db->prepare('INSERT INTO `rate_limit` (`id`, `release`) VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND))');
		$stmt->execute([$id, $seconds]);
		return null;
	}
	public static function CheckIP(int $limit, int $seconds, string $prefix = ''): ?int
	{
		$ip = Server::GetIP();
		if (is_null($ip)) return null;
		return self::Check($limit, $seconds, $prefix . $ip);
	}
}
