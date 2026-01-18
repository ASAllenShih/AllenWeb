<?php

namespace Allen\Basic\Util\Convert;

use Allen\Basic\Util\{Json, Language};

class Variable
{
	protected static array $_key_functions = [];
	public function __construct(
		protected array $key_functions = [],
	) {}
	public static function Add(string $name, ?callable $function): void
	{
		self::$_key_functions[$name] = $function;
	}
	public function _Add(string $name, ?callable $function): self
	{
		$this->key_functions[$name] = $function;
		return $this;
	}
	public static function Output(null|string|array $text, ?array $preg_functions = null): null|string|array
	{
		if (is_null($text)) return $text;
		$preg_functions ??= self::GetPregFunctions();
		return preg_replace_callback_array(
			$preg_functions,
			$text,
		);
	}
	public function _Output(null|string|array $text): null|string|array
	{
		return self::Output(
			text: $text,
			preg_functions: self::GetPregFunctions(
				key_functions: $this->key_functions,
			),
		);
	}
	public static function GetPregFunctions(
		array $key_functions = [],
		?array $key_functions_default = null,
	): array {
		$key_functions = array_filter(
			array_merge(
				$key_functions_default ?? self::$_key_functions,
				$key_functions,
			),
			fn($v) => is_callable($v),
		);
		return array_combine(
			array_map(
				fn($k) => '/\{\$' . preg_quote($k, '/') . '(?:\$(.*?)\$)?\}/',
				array_keys($key_functions),
			),
			array_map(
				fn($v) => function (array $matches) use ($v) {
					return $v($matches[1] ?? null) ?? '';
				},
				array_values($key_functions),
			),
		);
	}
	public static function Default(
		bool $lang = true,
		?string $lang_data_lang = null,
	): void {
		if ($lang) self::Add('lang', self::FunctionLang($lang_data_lang));
	}
	public static function FunctionLang(?string $lang = null): callable
	{
		return function (?string $data) use ($lang) {
			if (is_null($data) || !Json::Validate($data)) return null;
			$data = Json::Decode($data);
			if (!is_array($data)) return null;
			return Language::Output(
				data: $data,
				lang: $lang,
			);
		};
	}
}
