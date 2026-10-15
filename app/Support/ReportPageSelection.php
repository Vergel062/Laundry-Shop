<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportPageSelection
{
    public const PER_PAGE = 20;

    public static function validateRequest(Request $request): void
    {
        $request->validate([
            'export_mode' => ['nullable', Rule::in(['print', 'pdf'])],
            'export_pages' => ['required_with:export_mode', 'regex:/^(all|[1-9][0-9]*(\s*,\s*[1-9][0-9]*)*)$/'],
        ]);
    }

    /**
     * @return array{records: LengthAwarePaginator|Collection, total: int, pageCount: int}
     */
    public static function resolve(Builder $query, Request $request): array
    {
        $total = (clone $query)->count();
        $pageCount = (int) ceil($total / self::PER_PAGE);
        $exportMode = $request->input('export_mode');

        if (!$exportMode) {
            $records = $query->paginate(self::PER_PAGE)->appends($request->except('page'));

            return [
                'records' => $records,
                'total' => $records->total(),
                'pageCount' => $records->lastPage(),
            ];
        }

        $pageNames = $request->input('export_pages');
        $pages = $pageNames === 'all'
            ? ($pageCount > 0 ? range(1, $pageCount) : [])
            : array_values(array_unique(array_map('intval', preg_split('/\s*,\s*/', $pageNames))));

        foreach ($pages as $page) {
            if ($page < 1 || $page > $pageCount) {
                throw ValidationException::withMessages([
                    'export_pages' => "Choose page numbers between 1 and {$pageCount}.",
                ]);
            }
        }

        $records = collect();
        foreach ($pages as $page) {
            $records = $records->concat((clone $query)->forPage($page, self::PER_PAGE)->get());
        }

        return [
            'records' => $records->values(),
            'total' => $total,
            'pageCount' => $pageCount,
        ];
    }
}
