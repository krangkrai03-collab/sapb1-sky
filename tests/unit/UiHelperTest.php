<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pure-unit tests for ui_helper functions that don't need the database.
 *
 * @internal
 */
final class UiHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('ui');
    }

    public function testLocalDatetimeConvertsUtcToBangkok(): void
    {
        // App timezone is UTC; Bangkok is +7.
        $this->assertSame('2026-06-13 20:43:18', local_datetime('2026-06-13 13:43:18'));
    }

    public function testLocalDatetimeCustomFormat(): void
    {
        $this->assertSame('13/06/2026 20:43', local_datetime('2026-06-13 13:43:18', 'd/m/Y H:i'));
    }

    public function testLocalDatetimeEmptyReturnsEmpty(): void
    {
        $this->assertSame('', local_datetime(null));
        $this->assertSame('', local_datetime(''));
    }

    public function testLocalDatetimeInvalidReturnsInputUnchanged(): void
    {
        $this->assertSame('not-a-date', local_datetime('not-a-date'));
    }
}
