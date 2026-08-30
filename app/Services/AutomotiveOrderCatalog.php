<?php

namespace App\Services;

/**
 * Business mapping for automotive orders.  Keeping this mapping here avoids
 * duplicating product-type rules in controllers and JavaScript.
 */
class AutomotiveOrderCatalog
{
    public const AUTOMOTIVE_TREATMENTS = ['KACA_FILM', 'PPF', 'COATING', 'ANTI_KARAT', 'DETAILING'];
    public const BUILDING_TREATMENTS = ['KACA_FILM_GEDUNG'];

    private const PRODUCT_TYPE_BY_TREATMENT = [
        'KACA_FILM' => 'KACA_FILM',
        'PPF' => 'PPF',
        'COATING' => 'COATING',
        'ANTI_KARAT' => 'ANTI_KARAT',
        'DETAILING' => 'DETAILING',
        'KACA_FILM_GEDUNG' => 'KACA_FILM_GEDUNG',
    ];

    public static function productTypeCodeFor(string $treatmentCode): ?string
    {
        return self::PRODUCT_TYPE_BY_TREATMENT[$treatmentCode] ?? null;
    }

    public static function areaOptions(string $treatmentCode): array
    {
        switch ($treatmentCode) {
            case 'KACA_FILM':
                return ['Kaca Depan', 'Samping Kanan Depan', 'Samping Kiri Depan', 'Samping Kanan Belakang', 'Samping Kiri Belakang', 'Kaca Belakang', 'Sunroof', 'Lainnya'];
            case 'PPF':
                return ['Full Body', 'Hood', 'Roof', 'Front Bumper', 'Rear Bumper', 'Door', 'Fender', 'Mirror', 'Lainnya'];
            case 'COATING':
                return ['Full Body', 'Exterior', 'Interior', 'Glass', 'Wheel', 'Lainnya'];
            default:
                return [];
        }
    }

    public static function requiresArea(string $treatmentCode): bool
    {
        return in_array($treatmentCode, ['KACA_FILM', 'PPF', 'COATING'], true);
    }
}
