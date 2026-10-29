<?php

namespace Allen\Basic\Element;

use Allen\Basic\Element\Enum\ScriptType as EnumScriptType;
use Allen\Basic\Element\Trait\{ScriptType, Src};

class Script extends Element
{
	use Src, ScriptType;
	public function __construct(
		?string $src = null,
		?EnumScriptType $type = null,
		?string $content = null,
		bool $defer = false,
		bool $async = false,
	) {
		parent::__construct(
			tag: 'script',
			content: $content,
		);
		if ($defer) $this->AttributeAdd('defer');
		if ($async) $this->AttributeAdd('async');
		if (!is_null($src)) $this->SrcSet($src);
		if (!is_null($type)) $this->TypeSet($type);
	}
	public static function Module(
		?string $src = null,
		?string $content = null,
		bool $defer = false,
		bool $async = false,
	): self {
		$script = new self(
			src: $src,
			type: EnumScriptType::Module,
			content: $content,
			defer: $defer,
			async: $async,
		);
		return $script;
	}
}
