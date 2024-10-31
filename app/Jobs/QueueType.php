<?php 

namespace App\Jobs;

class QueueType
{
    public const URGENT = 'urgent';
    public const HIGH = 'high';
    public const DEFAULT = 'default';
    public const LOW = 'low';
    public const LONG = 'long';

    /**
     * Get all possible values of QueueType.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::URGENT,
            self::HIGH,
            self::DEFAULT,
            self::LOW,
            self::LONG,
        ];
    }

    /**
     * Check if a given value is a valid QueueType.
     *
     * @param string $value
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
