<?php

namespace Allen\Basic\Element;

use Allen\Basic\Element\Trait\ButtonType;
use Allen\Basic\Element\Enum\ButtonType as EnumButtonType;

class Button extends Element
{
	use ButtonType;
	public function __construct(
		?array $attribute = null,
		?string $content = null,
		?string $id = null,
		?array $class = null,
		?array $style = null,
		?bool $textContent = null,
		string|EnumButtonType $type = EnumButtonType::Button,
	) {
		parent::__construct(
			tag: 'button',
			attribute: $attribute,
			content: $content,
			id: $id,
			class: $class,
			style: $style,
			textContent: $textContent,
		);
		$this->TypeSet($type);
	}
}
