<?php

namespace App\Enums;

/**
 * System Adjustment Codes
 *
 * Defines all standard adjustment codes used throughout the booking system on every event.
 * These codes match the adjustment codes stored in the database.
 */
enum SystemAdjustment: string {
    case TAX = 'TAX';
    case SINGLE_TICKET_FEE = 'SINGLE_TICKET_FEE';
    case PAID_IN_FULL = 'PAID_IN_FULL';
    case CHOOSE_YOUR_CABIN = 'CHOOSE_YOUR_CABIN';
    case CARBON_OFFSET = 'CARBON_OFFSET';
    case CARBON_OFFSET_I = 'CARBON_OFFSET_I';
    case CARBON_OFFSET_B = 'CARBON_OFFSET_B';
    case CARBON_OFFSET_S = 'CARBON_OFFSET_S';
    case CARBON_OFFSET_O = 'CARBON_OFFSET_O';

    /**
     * Get the carbon offset code for a specific cabin category type
     *
     * @param string|null $categoryTypeFirstLetter First letter of category type (I, B, S, O)
     * @return self
     */
    public static function carbonOffset(?string $categoryTypeFirstLetter = null): self {
        if (!$categoryTypeFirstLetter) {
            return self::CARBON_OFFSET;
        }

        return match (strtoupper($categoryTypeFirstLetter)) {
            'I' => self::CARBON_OFFSET_I,
            'B' => self::CARBON_OFFSET_B,
            'S' => self::CARBON_OFFSET_S,
            'O' => self::CARBON_OFFSET_O,
            default => self::CARBON_OFFSET,
        };
    }
}
