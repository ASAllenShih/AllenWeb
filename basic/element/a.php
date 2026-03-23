<?php

namespace Allen\Basic\Element;

use Allen\Basic\Element\Trait\{Href, Target};
use Allen\Basic\Element\Enum\Target as EnumTarget;
use Allen\Basic\Util\Uri;

class A extends Element
{
	use Href;
	use Target;
	public function __construct(
		?array $attribute = null,
		?string $content = null,
		?string $id = null,
		?array $class = null,
		?array $style = null,
		?bool $textContent = null,
		?string $href = null,
		string|EnumTarget|null $target = null,
	) {
		parent::__construct(
			tag: 'a',
			attribute: $attribute,
			content: $content,
			id: $id,
			class: $class,
			style: $style,
			textContent: $textContent,
		);
		if (!is_null($href)) $this->HrefSet($href);
		if (!is_null($target)) $this->TargetSet($target);
	}
	public static function Link(
		?array $attribute = null,
		?string $content = null,
		?string $id = null,
		?array $class = null,
		?array $style = null,
		?bool $textContent = null,
		?string $link = null,
		bool $lang = false,
		bool $querySlash = false,
		bool $newTab = false,
	): self {
		return new self(
			attribute: $attribute,
			content: $content,
			id: $id,
			class: $class,
			style: $style,
			textContent: $textContent,
			href: Uri::Link(
				url: $link,
				lang: $lang,
				querySlash: $querySlash,
			),
			target: $newTab ? EnumTarget::Blank
				: null,
		);
	}
}
