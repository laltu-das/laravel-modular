<?php

declare(strict_types=1);

namespace Laltu\Modular\Api;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API response helper for modular applications.
 */
final class ApiResponse
{
    private array $meta = [];
    private ?string $message = null;
    private int $status = 200;
    private array $links = [];

    public static function make(): static
    {
        return new static();
    }

    public function status(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function message(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    public function links(array $links): static
    {
        $this->links = array_merge($this->links, $links);
        return $this;
    }

    public function module(string $module): static
    {
        $this->meta['module'] = $module;
        return $this;
    }

    /**
     * Return a success response.
     */
    public function success(mixed $data = null): JsonResponse
    {
        $payload = [
            'success' => true,
            'data' => $data,
        ];

        if ($this->message !== null) {
            $payload['message'] = $this->message;
        }

        if ($this->meta !== []) {
            $payload['meta'] = $this->meta;
        }

        if ($this->links !== []) {
            $payload['links'] = $this->links;
        }

        return response()->json($payload, $this->status);
    }

    /**
     * Return an error response.
     */
    public function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($this->meta !== []) {
            $payload['meta'] = $this->meta;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Return paginated data.
     */
    public function paginated(array $data, array $pagination): JsonResponse
    {
        return $this->status(200)->success([
            'items' => $data,
            'pagination' => $pagination,
        ]);
    }
}
