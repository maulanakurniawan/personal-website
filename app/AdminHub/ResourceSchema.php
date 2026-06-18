<?php

namespace App\AdminHub;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ResourceSchema
{
    public static function operations(array $schema): array
    {
        $ops = $schema['operations'] ?? $schema['actions'] ?? [];
        if (array_is_list($ops)) return $ops;
        return array_keys(array_filter($ops));
    }

    public static function supports(array $schema, string $operation): bool
    {
        return in_array($operation, self::operations($schema), true);
    }

    public static function fields(array $schema, string $purpose = 'detail'): array
    {
        $key = match ($purpose) { 'create' => 'create_fields', 'update', 'edit' => 'update_fields', 'list' => 'list_columns', default => 'fields' };
        $fields = $schema[$key] ?? null;
        if ($fields === null && $purpose === 'list') $fields = $schema['columns'] ?? null;
        if ($fields === null) $fields = $schema['fields'] ?? [];
        $normalized = self::normalizeFields($fields);
        if ($purpose === 'create') return array_values(array_filter($normalized, fn ($f) => ($f['creatable'] ?? true) && ($f['type'] ?? null) !== 'hidden'));
        if (in_array($purpose, ['update', 'edit'], true)) return array_values(array_filter($normalized, fn ($f) => ($f['editable'] ?? true) && ($f['type'] ?? null) !== 'hidden'));
        return array_values(array_filter($normalized, fn ($f) => !($f['hidden'] ?? false) && ($f['type'] ?? null) !== 'hidden'));
    }

    public static function normalizeFields(array $fields): array
    {
        $out = [];
        foreach ($fields as $key => $field) {
            if (is_string($field)) $field = ['key' => $field];
            if (! is_array($field)) continue;
            $field['key'] ??= is_string($key) ? $key : ($field['name'] ?? null);
            if (! $field['key']) continue;
            $field['name'] ??= $field['key'];
            $field['label'] ??= Str::headline((string) $field['key']);
            $field['type'] ??= 'text';
            $out[] = $field;
        }
        return $out;
    }

    public static function itemId(array $item): string|int|null
    {
        return $item['id'] ?? $item['uuid'] ?? $item['key'] ?? Arr::first($item);
    }

    public static function value(array $item, array $field): mixed
    {
        return data_get($item, $field['key'] ?? $field['name'] ?? '');
    }
}
