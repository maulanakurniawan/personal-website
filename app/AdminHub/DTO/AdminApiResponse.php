<?php

namespace App\AdminHub\DTO;

class AdminApiResponse
{
    public function __construct(
        public bool $success,
        public array $data = [],
        public array $meta = [],
        public ?array $error = null,
        public int $status = 200,
    ) {}

    public static function fromArray(array $payload, int $status = 200): self
    {
        return new self(
            (bool) ($payload['success'] ?? false),
            (array) ($payload['data'] ?? []),
            (array) ($payload['meta'] ?? []),
            self::normalizeError($payload),
            $status,
        );
    }

    private static function normalizeError(array $payload): ?array
    {
        if (! isset($payload['error'])) {
            return null;
        }
        $error = (array) $payload['error'];
        if (isset($payload['errors']) && ! isset($error['validation'])) {
            $error['validation'] = (array) $payload['errors'];
        }
        if (isset($error['errors']) && ! isset($error['validation'])) {
            $error['validation'] = (array) $error['errors'];
        }
        return $error;
    }
}

