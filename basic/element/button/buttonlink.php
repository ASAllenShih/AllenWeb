<?php

namespace Allen\Basic\Element\Button;

use Allen\Basic\Element\{A, Button};
use Allen\Basic\Element\Enum\Target;
use Allen\Basic\Util\{Uri};

class ButtonLink extends Button
{
	public static function Create(
		?string $content = null,
		?string $link = null,
		bool $lang = false,
		bool $newTab = false,
		bool $querySlash = false,
		?string $id = null,
		?array $class = null,
		?array $style = null,
		?bool $textContent = null,
	): self {
		return new self(
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
			target: $newTab ? Target::Blank
				: null,
		);
	}
	protected A $a;
	public function __construct(
		?array $attribute = null,
		?string $content = null,
		?string $id = null,
		?array $class = null,
		?array $style = null,
		?bool $textContent = null,
		?string $href = null,
		string|Target|null $target = null,
	) {
		parent::__construct(
			attribute: $attribute,
			content: $content,
			id: $id,
			class: $class,
			style: $style,
			textContent: $textContent,
		);
		$this->a = new A(
			href: $href,
			target: $target,
		);
	}
	public function Render(): string
	{
		$render = parent::Render();
		$this->a->ContentSet($render);
		return $this->a->Render();
	}
}
