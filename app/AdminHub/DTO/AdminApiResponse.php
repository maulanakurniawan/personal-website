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
            isset($payload['error']) ? (array) $payload['error'] : null,
            $status,
        );
    }
}
