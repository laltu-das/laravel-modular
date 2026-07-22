<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

/**
 * Base implementation of the Message contract.
 *
 * Modules can extend this class for their message contracts.
 * Place messages in the module's Contracts/ or Messages/ directory.
 *
 * Example:
 * ```php
 * // Modules/Orders/Contracts/OrderPlaced.php
 * final readonly class OrderPlaced extends BaseMessage
 * {
 *     public function __construct(
 *         public string $orderId,
 *         public int $customerId,
 *         public float $total,
 *     ) {}
 *
 *     public function channel(): string
 *     {
 *         return 'orders';
 *     }
 * }
 * ```
 */
abstract class BaseMessage implements Message
{
    /**
     * Get the channel/queue name for this message.
     * Defaults to the snake_case class name without "Message" suffix.
     */
    public function channel(): string
    {
        $class = class_basename(static::class);
        $class = preg_replace('/Message$/', '', $class) ?? $class;

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class) ?? $class);
    }

    /**
     * Get the message priority.
     */
    public function priority(): int
    {
        return 0;
    }

    /**
     * Get the delay in seconds.
     */
    public function delay(): int
    {
        return 0;
    }

    /**
     * Get the queue connection.
     */
    public function connection(): ?string
    {
        return null;
    }

    /**
     * Get the queue name (alias for channel for Laravel Queue compatibility).
     */
    public function queue(): string
    {
        return $this->channel();
    }

    /**
     * Convert the message to an array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [];

        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $data[$prop->getName()] = $prop->getValue($this);
        }

        return $data;
    }

    /**
     * Create a message instance from an array (for deserialization).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }
}
