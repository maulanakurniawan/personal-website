<?php

namespace App\AdminHub;

use Illuminate\Support\Str;

class FieldRenderer
{
    public static function display(mixed $value, array $field = [], bool $table = false): string
    {
        if ($value === null || $value === '') return '<span class="text-slate-400">—</span>';
        $type = $field['type'] ?? 'text';
        return match ($type) {
            'boolean' => self::badge($value ? 'Yes' : 'No', $value ? 'green' : 'slate'),
            'badge', 'status', 'select' => self::badge((string) $value, self::statusColor((string) $value)),
            'email' => '<a class="text-blue-700 hover:underline" href="mailto:'.e((string) $value).'">'.e((string) $value).'</a>',
            'url' => '<a class="text-blue-700 hover:underline" href="'.e((string) $value).'" target="_blank" rel="noopener">'.e($table ? Str::limit((string) $value, 42) : (string) $value).'</a>',
            'money' => e(is_numeric($value) ? number_format((float) $value, 2) : (string) $value),
            'number' => e(is_numeric($value) ? number_format((float) $value) : (string) $value),
            'date', 'datetime' => e(self::date((string) $value, $type)),
            'json' => self::json($value, $table),
            'textarea' => e($table ? Str::limit((string) $value, 120) : (string) $value),
            default => e($table ? Str::limit((string) $value, 80) : (string) $value),
        };
    }

    private static function badge(string $text, string $color): string { return '<span class="inline-flex rounded-full bg-'.$color.'-100 px-2 py-1 text-xs font-medium text-'.$color.'-800">'.e($text).'</span>'; }
    private static function statusColor(string $value): string { return match (Str::lower($value)) { 'active','paid','success','enabled' => 'green', 'pending','trialing' => 'amber', 'failed','error','disabled','deleted','inactive' => 'red', default => 'slate' }; }
    private static function date(string $value, string $type): string { try { return \Illuminate\Support\Carbon::parse($value)->format($type === 'date' ? 'M j, Y' : 'M j, Y g:i A'); } catch (\Throwable) { return $value; } }
    private static function json(mixed $value, bool $table): string { $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); return $table ? '<code class="text-xs">'.e(Str::limit($json ?: '', 80)).'</code>' : '<details class="rounded bg-slate-50 p-3"><summary class="cursor-pointer">View JSON</summary><pre class="mt-2 whitespace-pre-wrap text-xs">'.e($json ?: '').'</pre></details>'; }
}
