<?php

namespace Tests\Unit;

use App\Services\DatabaseConsoleService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DatabaseConsoleServiceTest extends TestCase
{
    #[Test]
    public function it_recognizes_read_only_queries(): void
    {
        $service = new DatabaseConsoleService();

        $this->assertTrue($service->isReadOnly('SELECT * FROM users'));
        $this->assertTrue($service->isReadOnly('SHOW TABLES'));
        $this->assertTrue($service->isReadOnly('DESCRIBE users'));
        $this->assertTrue($service->isReadOnly('EXPLAIN SELECT * FROM posts'));
        $this->assertFalse($service->isReadOnly('UPDATE users SET name = "x" WHERE id = 1'));
        $this->assertFalse($service->isReadOnly('DELETE FROM users WHERE id = 1'));
    }

    #[Test]
    public function it_ignores_leading_sql_comments_when_detecting_query_type(): void
    {
        $service = new DatabaseConsoleService();

        $this->assertTrue($service->isReadOnly("-- test\nSELECT 1"));
        $this->assertFalse($service->isReadOnly("/* test */ UPDATE users SET name = 'x' WHERE id = 1"));
    }

    #[Test]
    public function it_blocks_multiple_statements_but_allows_semicolons_inside_strings(): void
    {
        $service = new DatabaseConsoleService();
        $method = new ReflectionMethod(DatabaseConsoleService::class, 'containsMultipleStatements');

        $this->assertFalse($method->invoke($service, "SELECT 'a;b';"));
        $this->assertFalse($method->invoke($service, 'SELECT 1;'));
        $this->assertTrue($method->invoke($service, 'SELECT 1; DELETE FROM users;'));
    }

    #[Test]
    public function write_queries_require_explicit_confirmation_before_database_access(): void
    {
        $service = new DatabaseConsoleService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Yazma sorgusu onayini');

        $service->execute("UPDATE users SET name = 'x' WHERE id = 1", false);
    }
}
