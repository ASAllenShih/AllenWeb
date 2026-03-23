<?php

namespace Allen\Basic\Util;

use Allen\Basic\Path;

class Menu
{
	static private array|null $items = null;
	static public function Get(): array
	{
		if (self::$items === null) {
			$domain = Server::GetDomain();
			if ($domain !== null && is_file(Path::Setting('menu/' . $domain . '.php'))) {
				self::$items = require Path::Setting('menu/' . $domain . '.php');
			} else if (is_file(Path::Setting('menu/default.php'))) {
				self::$items = require Path::Setting('menu/default.php');
			}
			self::$items = is_array(self::$items) ? self::$items : [];
		}
		return self::$items;
	}
	static public function Add(array $menu): void
	{
		self::$items = array_merge_recursive(self::Get(), $menu);
	}
	static public function Output(?array $menu = null): string
	{
		if (empty($menu)) return '';
		$output = '<ul>';
		foreach ($menu ?? [] as $name => $item) {
			if (is_string($item)) {
				$output .= self::ListItem($name, $item);
			} else if (is_array($item)) {
				$url = $item[0] ?? null;
				$output .= self::ListItem($name, $url, $item);
			}
		}
		$output .= '</ul>';
		return $output;
	}
	static public function Sitemap(): array
	{
		$sitemaps = self::ListSitemap(self::Get());
		return array_values(array_map(fn($item) => 'https://' . Server::GetDomain() . $item, array_filter(array_unique($sitemaps), fn($item) => str_starts_with($item, '/'))));
	}
	static protected function ListSitemap(array $menu): array
	{
		$sitemaps = [];
		foreach ($menu as $value) {
			if (is_string($value)) {
				$sitemaps[] = $value;
			} else if (is_array($value)) {
				$sitemaps = array_merge($sitemaps, self::ListSitemap($value));
			}
		}
		return $sitemaps;
	}
	static protected function ListItem(string $name, ?string $url = null, ?array $children = null): string
	{
		if (!empty($children)) $children = array_filter($children, fn($item) => !is_int($item), \ARRAY_FILTER_USE_KEY);
		$output = '<li><div>';
		$output .= self::Text($name, $url);
		if (!empty($children)) $output .= self::ChildrenText();
		$output .= '</div>';
		if (!empty($children)) $output .= self::Output($children);
		$output .= '</li>';
		return $output;
	}
	static protected function Text(string $name, ?string $url = null): string
	{
		$link = self::TextLink($url);
		$output = $name;
		if (!empty($link)) $output = '<a href="' . $link . '">' . $output . '</a>';
		return $output;
	}
	static protected function TextLink(?string $url = null): ?string
	{
		if ($url !== null) return $url;
		else if (Browser::IsSafari() || Config::Get('web.start.menu.static', false)) return 'javascript:void(0);';
		return null;
	}
	static protected function ChildrenText(): string
	{
		$output = '<span class="material-symbols-outlined more">expand_more</span>';
		$output .= '<span class="material-symbols-outlined less">keyboard_arrow_up</span>';
		return $output;
	}
}
