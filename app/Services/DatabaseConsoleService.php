<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseConsoleService
{
    public const MAX_ROWS = 500;

    /**
     * @return array{
     *     read_only: bool,
     *     columns: array<int, string>,
     *     rows: array<int, array<string, mixed>>,
     *     affected: int|null,
     *     elapsed_ms: float,
     *     truncated: bool
     * }
     */
    public function execute(string $sql, bool $writeConfirmed = false): array
    {
        $sql = trim($sql);

        if ($sql === '') {
            throw new \InvalidArgumentException('SQL sorgusu bos olamaz.');
        }

        if ($this->containsMultipleStatements($sql)) {
            throw new \RuntimeException('Guvenlik nedeniyle tek seferde yalnizca bir SQL sorgusu calistirilabilir.');
        }

        $readOnly = $this->isReadOnly($sql);

        if (! $readOnly && ! $writeConfirmed) {
            throw new \RuntimeException('Bu sorgu veritabanini degistirebilir. Yazma sorgusu onayini isaretleyip tekrar calistirin.');
        }

        $pdo = DB::connection()->getPdo();
        $startedAt = microtime(true);
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $elapsedMs = round((microtime(true) - $startedAt) * 1000, 2);

        if (! $readOnly) {
            return [
                'read_only' => false,
                'columns' => [],
                'rows' => [],
                'affected' => $statement->rowCount(),
                'elapsed_ms' => $elapsedMs,
                'truncated' => false,
            ];
        }

        $rows = [];
        $columns = [];
        $truncated = false;

        while (($row = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            if ($columns === []) {
                $columns = array_keys($row);
            }

            if (count($rows) >= self::MAX_ROWS) {
                $truncated = true;
                break;
            }

            $rows[] = array_map([$this, 'normalizeValue'], $row);
        }

        return [
            'read_only' => true,
            'columns' => $columns,
            'rows' => $rows,
            'affected' => null,
            'elapsed_ms' => $elapsedMs,
            'truncated' => $truncated,
        ];
    }

    public function isReadOnly(string $sql): bool
    {
        $keyword = $this->firstKeyword($sql);

        return in_array($keyword, [
            'SELECT',
            'SHOW',
            'DESCRIBE',
            'DESC',
            'EXPLAIN',
        ], true);
    }

    public function firstKeyword(string $sql): string
    {
        $sql = ltrim($this->stripLeadingComments($sql));

        if (! preg_match('/^([A-Za-z]+)/', $sql, $matches)) {
            return '';
        }

        return Str::upper($matches[1]);
    }

    private function stripLeadingComments(string $sql): string
    {
        do {
            $before = $sql;
            $sql = preg_replace('/^\s*--[^\r\n]*(?:\r?\n|$)/', '', $sql) ?? $sql;
            $sql = preg_replace('/^\s*#[^\r\n]*(?:\r?\n|$)/', '', $sql) ?? $sql;
            $sql = preg_replace('/^\s*\/\*.*?\*\/\s*/s', '', $sql) ?? $sql;
        } while ($sql !== $before);

        return $sql;
    }

    private function containsMultipleStatements(string $sql): bool
    {
        $single = false;
        $double = false;
        $backtick = false;
        $escaped = false;
        $statementEnded = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if (($single || $double) && $char === '\\') {
                $escaped = true;
                continue;
            }

            if (! $double && ! $backtick && $char === "'") {
                $single = ! $single;
                continue;
            }

            if (! $single && ! $backtick && $char === '"') {
                $double = ! $double;
                continue;
            }

            if (! $single && ! $double && $char === '`') {
                $backtick = ! $backtick;
                continue;
            }

            if ($single || $double || $backtick) {
                continue;
            }

            if ($char === ';') {
                $statementEnded = true;
                continue;
            }

            if ($statementEnded && ! ctype_space($char)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_resource($value)) {
            return '[resource]';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_string($value) && mb_strlen($value) > 5000) {
            return Str::limit($value, 5000, '…');
        }

        return $value;
    }
}
