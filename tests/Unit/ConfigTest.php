<?php

namespace AiAltText\Tests\Unit;

use PHPUnit\Framework\TestCase;
use AiAltText\Config;
use AiAltText\Constants;

class ConfigTest extends TestCase
{
    public function test_get_provider_returns_default_when_no_option_is_set()
    {
        $this->assertSame(Constants::DEFAULT_PROVIDER, Config::get_provider());
    }
} 