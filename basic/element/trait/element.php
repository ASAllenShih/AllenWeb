<?php

namespace Allen\Basic\Element\Trait;

trait Element
{
	protected string $tag;
	protected bool $selfClose;
	protected array $attribute = [];
	protected string $content = '';
	/**
	 * 取得標籤名稱
	 */
	public function TagGet(): string
	{
		return $this->tag;
	}
	/**
	 * 是否為自閉合標籤
	 */
	public function IsSelfClose(): bool
	{
		return $this->selfClose;
	}
	/**
	 * 取得內容
	 */
	public function ContentGet(): string
	{
		return $this->content;
	}
	/**
	 * 新增內容
	 */
	public function ContentAdd(string|Element ...$content): self
	{
		$this->content .= implode(\PHP_EOL, array_map(fn($item) => $item instanceof Element ? $item->Render() : $item, $content));
		return $this;
	}
	/**
	 * 設定內容
	 */
	public function ContentSet(string ...$content): self
	{
		$this->content = '';
		return $this->ContentAdd(...$content);
	}
	public function TextContentAdd(string ...$content): self
	{
		return $this->ContentAdd(...array_map(fn($item) => htmlspecialchars($item), $content));
	}
	public function TextContentSet(string ...$content): self
	{
		return $this->ContentSet(...array_map(fn($item) => htmlspecialchars($item), $content));
	}
	/**
	 * 取得所有屬性
	 */
	public function AttributeGetAll(): array
	{
		return $this->attribute;
	}
	/**
	 * 取得屬性
	 */
	public function AttributeGet(string $key): ?string
	{
		return $this->AttributeGetAll()[$key] ?? null;
	}
	public function AttributeHas(string $key): bool
	{
		return array_key_exists($key, $this->AttributeGetAll());
	}
	/**
	 * 設定屬性
	 */
	public function AttributeAdd(string $key, ?string $value = null): self
	{
		$this->attribute[$key] = $value;
		return $this;
	}
	/**
	 * 設定所有屬性
	 */
	public function AttributeAddAll(array $attributes): self
	{
		foreach ($attributes as $key => $value) {
			$this->AttributeAdd($key, $value);
		}
		return $this;
	}
	/**
	 * 移除屬性
	 */
	public function AttributeRemove(string $key): self
	{
		unset($this->attribute[$key]);
		return $this;
	}
	/**
	 * 清除所有屬性
	 */
	public function AttributeClear(): self
	{
		$this->attribute = [];
		return $this;
	}
	/**
	 * 將屬性值轉換為陣列
	 */
	static public function _AttributeToArray(
		?string $value,
		bool $returnNull = true,
		bool $semicolon = false,
		bool $space = false,
		bool $keyColon = false,
	): ?array {
		$value = is_string($value) ? trim($value) : null;
		$output = null;
		if (!empty($value)) {
			if ($semicolon) {
				$output = preg_split('/\s*;\s*/', $value);
			} else if ($space) {
				$output = preg_split('/\s+/', $value);
			}
		}
		if (empty($output) || !is_array($output)) {
			return $returnNull ? null : [];
		} else if ($keyColon) {
			$output = array_column(array_map(fn($item) => array_map('trim', explode(':', $item, 2)), $output), 1, 0);
		}
		return $output;
	}
	/**
	 * 將屬性鍵轉換為陣列
	 */
	public function _AttributeKeyToArray(
		string $key,
		bool $returnNull = true,
		bool $semicolon = false,
		bool $space = false,
		bool $keyColon = false,
	): ?array {
		return $this::_AttributeToArray(
			value: $this->AttributeGet($key),
			returnNull: $returnNull,
			semicolon: $semicolon,
			space: $space,
			keyColon: $keyColon,
		);
	}
	/**
	 * 將陣列轉換為屬性鍵
	 */
	static public function _AttributeFromArray(
		?array $value,
		bool $returnNull = false,
		bool $semicolon = false,
		bool $space = false,
		bool $keyColon = false,
	): ?string {
		$output = null;
		if (!empty($value)) {
			if ($keyColon) {
				$value = array_map(fn($k, $v) => $k . ': ' . $v, array_keys($value), array_values($value));
			}
			if ($semicolon) {
				$output = implode('; ', $value);
			} else if ($space) {
				$output = implode(' ', $value);
			}
		}
		if (empty($output) || !is_string($output)) return $returnNull ? null : '';
		return $output;
	}
	/**
	 * 將陣列轉換為屬性
	 */
	public function _AttributeKeyFromArray(
		string $key,
		?array $value = null,
		bool $returnNull = false,
		bool $semicolon = false,
		bool $space = false,
		bool $keyColon = false,
	): self {
		$output = $this::_AttributeFromArray(
			value: $value,
			returnNull: $returnNull,
			semicolon: $semicolon,
			space: $space,
			keyColon: $keyColon,
		);
		if (!empty($output)) {
			$this->AttributeAdd($key, $output);
		} else if ($returnNull) {
			$this->AttributeAdd($key, null);
		} else {
			$this->AttributeRemove($key);
		}
		return $this;
	}
	/**
	 * 取得 ID
	 */
	public function IdGet(): ?string
	{
		return $this->AttributeGet('id');
	}
	/**
	 * 設定 ID
	 */
	public function IdSet(?string $id = null): self
	{
		if (!empty($id)) {
			$this->AttributeAdd('id', $id);
		} else {
			$this->AttributeRemove('id');
		}
		return $this;
	}
	/**
	 * 設定 ID 新增前綴
	 */
	public function IdAddPrefix(?string $prefix = null): self
	{
		if (!empty($prefix)) {
			$this->IdSet($prefix . '-' . $this->IdGet());
		}
		return $this;
	}
	/**
	 * 取得所有 Class
	 */
	public function ClassGet(bool $returnNull = true): ?array
	{
		return $this->_AttributeKeyToArray(
			key: 'class',
			returnNull: $returnNull,
			space: true,
		);
	}
	/**
	 * 是否擁有指定 Class
	 */
	public function ClassHas(string $class): bool
	{
		$classes = $this->ClassGet(
			returnNull: false,
		);
		return in_array($class, $classes);
	}
	/**
	 * 設定所有 Class
	 */
	public function ClassSet(string ...$classes): self
	{
		return $this->_AttributeKeyFromArray(
			key: 'class',
			value: $classes,
			space: true,
		);
	}
	/**
	 * 新增 Class
	 */
	public function ClassAdd(string ...$classes): self
	{
		$current_classes = $this->ClassGet(
			returnNull: false,
		);
		return $this->ClassSet(...array_unique(array_merge($current_classes, $classes)));
	}
	/**
	 * 移除 Class
	 */
	public function ClassRemove(string ...$classes): self
	{
		$current_classes = $this->ClassGet(
			returnNull: false,
		);
		return $this->ClassSet(...array_diff($current_classes, $classes));
	}
	/**
	 * 取得樣式
	 */
	public function StyleGet(bool $returnNull = true): array
	{
		return $this->_AttributeKeyToArray(
			key: 'style',
			returnNull: $returnNull,
			semicolon: true,
			keyColon: true,
		);
	}
	/**
	 * 設定所有樣式
	 */
	public function StyleSet(?array $styles = null): self
	{
		return $this->_AttributeKeyFromArray(
			key: 'style',
			value: $styles,
			semicolon: true,
			keyColon: true,
		);
	}
	/**
	 * 新增樣式
	 */
	public function StyleAdd(string $key, ?string $value = null): self
	{
		return $this->StyleAddAll([$key => $value]);
	}
	/**
	 * 新增多個樣式
	 */
	public function StyleAddAll(array $styles): self
	{
		$current_styles = $this->StyleGet(returnNull: false);
		return $this->StyleSet(array_merge($current_styles, $styles));
	}
	/**
	 * 渲染元素
	 */
	public function Render(): string
	{
		return '<' .
			$this->TagGet() . // 標籤
			implode(
				'',
				array_map( // 屬性
					fn($key, $value) => ' ' . htmlspecialchars($key) . (is_string($value) ? '="' . htmlspecialchars($value) . '"' : ''),
					array_keys($this->AttributeGetAll()),
					$this->AttributeGetAll(),
				),
			) .
			'>' .
			// 標籤
			($this->IsSelfClose() ?
				'' : // 自閉合標籤
				$this->ContentGet() . // 內容
				'</' .
				$this->TagGet() . // 關閉標籤
				'>');
	}
	public function __toString(): string
	{
		return $this->Render();
	}
}
