<?php

namespace Allen\Basic\Element;

use Allen\Basic\Element\Trait\Element as TraitElement;

class Element
{
	use TraitElement;
	public function __construct(
		string $tag = 'div',
		bool $selfClose = false,
		?string $content = null,
		?array $attribute = null,
		?string $id = null,
		?array $class = null,
		?array $style = null,
		?bool $textContent = null,
	) {
		$this->tag = $tag;
		$this->selfClose = $selfClose;
		if (!is_null($content)) $textContent ? $this->TextContentAdd($content) : $this->ContentAdd($content);
		if (!is_null($attribute)) $this->AttributeAddAll($attribute);
		if (!is_null($id)) $this->IdSet($id);
		if (!is_null($class)) $this->ClassAdd(...$class);
		if (!is_null($style)) $this->StyleAddAll($style);
	}
}
