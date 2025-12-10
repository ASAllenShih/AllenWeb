<?php

namespace Allen\Basic\API;

use Allen\Basic\API;

class Filter
{
	public static function Callback(array $data, ?callable $callback = null): array
	{
		return array_values(array_filter(
			$data,
			fn($item) => self::ResultCallback(
				item: $item,
				callback: $callback,
			),
		));
	}
	public static function Regex(array $data, ?string $regex = null, ?callable $item_callback = null): array
	{
		return self::Callback(
			data: $data,
			callback: fn($item) => self::ResultRegex(
				item: self::DataItem(
					item: $item,
					item_callback: $item_callback,
				),
				regex: $regex,
			),
		);
	}
	public static function InArray(array $data, ?array $array = null, ?callable $item_callback = null): array
	{
		return self::Callback(
			data: $data,
			callback: fn($item) => self::ResultInArray(
				item: self::DataItem(
					item: $item,
					item_callback: $item_callback,
				),
				array: $array,
			),
		);
	}
	public static function RegexQuery(array $data, ?string $query = null, ?callable $item_callback = null): array
	{
		return self::Callback(
			data: $data,
			callback: fn($item) => self::ResultRegexQuery(
				item: self::DataItem(
					item: $item,
					item_callback: $item_callback,
				),
				query_data: self::DataQuery(
					query: $query,
				),
			),
		);
	}
	public static function InArrayQuery(array $data, ?string $query = null, ?callable $item_callback = null): array
	{
		return self::Callback(
			data: $data,
			callback: fn($item) => self::ResultInArrayQuery(
				item: self::DataItem(
					item: $item,
					item_callback: $item_callback,
				),
				query_data: self::DataQuery(
					query: $query,
				),
			),
		);
	}
	public static function Query(array $data, ?string $query = null, ?callable $item_callback = null, bool $regex = true, bool $in_array = true): array
	{
		$query_data = self::DataQuery(
			query: $query,
		);
		return self::Callback(
			data: $data,
			callback: fn($item) => !(
				!$regex ?: !self::ResultRegexQuery(
					item: self::DataItem(
						item: $item,
						item_callback: $item_callback,
					),
					query_data: $query_data,
				)
				|| !$in_array ?: !self::ResultInArrayQuery(
					item: self::DataItem(
						item: $item,
						item_callback: $item_callback,
					),
					query_data: $query_data,
				)
			),
		);
	}
	public static function Select(array $data, ?array $select = null): array
	{
		if (is_null($select)) {
			return $data;
		} else if (count($select) === 0) {
			return [];
		} else if (!in_array(false, array_map(
			fn($k) => is_int($k),
			array_keys($data),
		)) && !in_array(false, array_map(
			fn($v) => is_array($v),
			$data,
		))) {
			return array_map(
				fn($item) => self::Select(
					data: $item,
					select: $select,
				),
				$data,
			);
		}
		$output = [];
		foreach ($data as $data_k => $data_v) {
			if (in_array($data_k, $select)) {
				$output[$data_k] = $data_v;
				continue;
			}
			if (is_array($data_v)) {
				$sub_select = array_filter($select, fn($value) => str_starts_with($value, $data_k . '/'));
				if (count($sub_select) > 0) {
					$sub_output = self::Select(
						data: $data_v,
						select: array_map(
							fn($value) => substr($value, strlen($data_k) + 1),
							$sub_select,
						),
					);
					if (count($sub_output) > 0) {
						$output[$data_k] = $sub_output;
					}
					continue;
				}
			}
		}
		return $output;
	}
	public static function SelectString(array $data, null|string|array $select = null): array
	{
		$select = is_string($select) ? array_map('trim', explode(',', $select)) : $select;
		return self::Select(
			data: $data,
			select: $select,
		);
	}
	public static function SelectQuery(array $data, string $query = 'select'): array
	{
		$select = API::InputQuery(
			query: $query,
			required: false,
		);
		return self::SelectString(
			data: $data,
			select: $select,
		);
	}
	public function __construct(
		public array $data,
	) {}
	public function _Callback(?callable $callback = null): self
	{
		$this->data = self::Callback(
			data: $this->data,
			callback: $callback,
		);
		return $this;
	}
	public function _Regex(?string $regex = null, ?callable $item_callback = null): self
	{
		$this->data = self::Regex(
			data: $this->data,
			regex: $regex,
			item_callback: $item_callback,
		);
		return $this;
	}
	public function _RegexQuery(?string $query = null, ?callable $item_callback = null): self
	{
		$this->data = self::RegexQuery(
			data: $this->data,
			query: $query,
			item_callback: $item_callback,
		);
		return $this;
	}
	public function _Select(?array $select = null): self
	{
		$this->data = self::Select(
			data: $this->data,
			select: $select,
		);
		return $this;
	}
	public function _SelectString(null|string|array $select = null): self
	{
		$this->data = self::SelectString(
			data: $this->data,
			select: $select,
		);
		return $this;
	}
	public function _SelectQuery(string $query = 'select'): self
	{
		$this->data = self::SelectQuery(
			data: $this->data,
			query: $query,
		);
		return $this;
	}
	public function _Get(): array
	{
		return $this->data;
	}
	protected static function DataItem(mixed $item, ?callable $item_callback = null): mixed
	{
		return is_callable($item_callback)
			? $item_callback($item)
			: $item;
	}
	protected static function DataQuery(?string $query = null): null|string|array
	{
		return is_string($query)
			? API::InputQuery(
				query: $query,
				required: false,
			)
			: null;
	}
	protected static function ResultCallback(mixed $item, ?callable $callback = null): bool
	{
		return is_callable($callback)
			? $callback($item)
			: true;
	}
	protected static function ResultRegex(mixed $item, ?string $regex = null): bool
	{
		return is_string($regex)
			? preg_match(
				'/^' . $regex . '$/',
				strval($item),
			) === 1
			: true;
	}
	protected static function ResultInArray(mixed $item, ?array $array = null): bool
	{
		return is_array($array)
			? in_array(
				$item,
				$array,
			)
			: true;
	}
	protected static function ResultRegexQuery(mixed $item, null|string|array $query_data = null): bool
	{
		return is_string($query_data)
			? self::ResultRegex(
				item: $item,
				regex: $query_data,
			)
			: (is_array($query_data)
				? in_array(
					true,
					array_map(
						fn($q) => self::ResultRegex(
							item: $item,
							regex: $q,
						),
						$query_data,
					),
				)
				: true
			);
	}
	protected static function ResultInArrayQuery(mixed $item, null|string|array $query_data = null): bool
	{
		return is_string($query_data)
			? self::ResultInArray(
				item: $item,
				array: array_map('trim', explode(',', $query_data)),
			)
			: (is_array($query_data)
				? self::ResultInArray(
					item: $item,
					array: $query_data,
				)
				: true
			);
	}
}
