<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Builds warranty numbers as LEX-{year}-{sequence}, e.g. LEX-2026-00001.
 *
 * The sequence restarts at 00001 every year. It is padded to 5 digits as a
 * minimum only, so past 99999 it simply grows (LEX-2026-100000) and stays
 * unique. The next number is found numerically (not by string order, where
 * "100000" would sort before "99999"). Older codes such as WR2026004 do not
 * match the prefix and are left alone.
 *
 * Call this inside the transaction that inserts the warranty: the row lock
 * serialises concurrent callers, and the unique index on kode_warranty is the
 * final guard (callers retry on a duplicate).
 */
class WarrantyCodeGenerator
{
    public const PREFIX = 'LEX';

    public function next(?CarbonInterface $at = null): string
    {
        $year = ($at ?? now())->format('Y');
        $prefix = self::PREFIX . '-' . $year . '-';

        $last = (int) DB::table('warranties')
            ->where('kode_warranty', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(kode_warranty, ?) AS UNSIGNED)) AS last_number', [strlen($prefix) + 1])
            ->value('last_number');

        return $prefix . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
    }

    /** True when a failed insert was caused by a clashing warranty number. */
    public static function isDuplicateCodeError(\Throwable $e): bool
    {
        return $e instanceof \Illuminate\Database\QueryException
            && str_contains(strtolower($e->getMessage()), 'kode_warranty');
    }
}
