<?php

namespace Allen\Basic\Util\Integration\Ai\Data\Message;

enum Role: string
{
	case Developer = 'developer';
	case System = 'system';
	case User = 'user';
	case Assistant = 'assistant';
}
