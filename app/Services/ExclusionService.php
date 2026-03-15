<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * ExclusionService
 *
 * Applies excluded-numbers filtering to any CallLog Builder query.
 *
 * Strategy: NOT EXISTS subquery that cross-references the call row's own
 * organization_id against the excluded_numbers and organization_settings
 * tables.  This handles single-org, cross-org admin, and mobile contexts
 * uniformly — no PHP-side pre-loading required.
 *
 * Usage:
 *   ExclusionService::applyToQuery($query);
 *
 * The filter is entirely a SQL construct, so it is applied BEFORE any
 * GROUP BY / aggregation and works correctly with lazy-loading streams.
 */
class ExclusionService
{
    /**
     * Append the excluded-numbers WHERE NOT EXISTS clause to $query.
     *
     * The subquery excludes any call_log row whose caller_number appears in
     * the excluded_numbers list for the row's own organization, but only when
     * that organization has exclude_numbers_enabled = 1.
     *
     * @param  Builder   $query   CallLog query builder (mutated in-place)
     * @param  int|null  $orgId   Pass a known org ID for a small index hint;
     *                            pass null to let the subquery self-join on
     *                            call_logs.organization_id (works for all roles).
     */
    public static function applyToQuery(Builder $query, ?int $orgId = null): void
    {
        $query->whereNotExists(function ($sub) use ($orgId) {
            $sub->select(DB::raw(1))
                ->from('excluded_numbers')
                ->join(
                    'organization_settings',
                    'organization_settings.organization_id',
                    '=',
                    'excluded_numbers.organization_id'
                )
                ->whereColumn('excluded_numbers.phone_number', 'call_logs.caller_number')
                ->where('organization_settings.exclude_numbers_enabled', 1);

            if ($orgId !== null) {
                // Scope to the specific organization — allows MySQL index seek
                $sub->where('excluded_numbers.organization_id', $orgId);
            } else {
                // Let each row self-scope: handles cross-org admin views
                $sub->whereColumn('excluded_numbers.organization_id', 'call_logs.organization_id');
            }
        });
    }
}
