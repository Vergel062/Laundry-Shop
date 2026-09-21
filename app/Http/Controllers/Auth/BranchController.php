<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchController extends Controller
{
    public function index()
    {
        // 1. Auto-Delete Policy: Permanenteng buburahin ang mga archive na lagpas 30 days
        DB::table('branches')
            ->whereNotNull('archive_date')
            ->where('archive_date', '<', now()->subDays(30))
            ->delete();

        // Kunin ang mga active branches
        $branches = DB::table('branches')->whereNull('archive_date')->orderBy('id', 'asc')->get();

        return view('admin.branch_management', compact('branches'));
    }

    public function archived()
    {
        $archived = DB::table('branches')
            ->whereNotNull('archive_date')
            ->where('archive_date', '>=', now()->subDays(30))
            ->orderByDesc('archive_date')
            ->get();

        return view('admin.archived_branches', compact('archived'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        // 1. I-insert ang branch
        $branchData = [
            'branch_name' => $request->branch_name,
            'location' => $request->location,
        ];

        if (Schema::hasColumn('branches', 'created_at')) {
            $branchData['created_at'] = now();
        }
        if (Schema::hasColumn('branches', 'updated_at')) {
            $branchData['updated_at'] = now();
        }

        $new_branch_id = DB::table('branches')->insertGetId($branchData);

        // 2. Otomatikong lagyan ng default inventory items ang bagong branch
        $default_items = [
            ['Detergent', 'Sachets', 2],
            ['Fabric Spray', 'Gallons', 2],
            ['LPG', 'Tanks', 5],
            ['Downy / Softener', 'Sachets', 5]
        ];

        foreach ($default_items as $item) {
            $inventoryData = [
                'branch_id' => $new_branch_id,
                'item_name' => $item[0],
                'stock_level' => 0,
                'unit' => $item[1],
                'min_threshold' => $item[2],
            ];

            if (Schema::hasColumn('inventory', 'created_at')) {
                $inventoryData['created_at'] = now();
            }
            if (Schema::hasColumn('inventory', 'updated_at')) {
                $inventoryData['updated_at'] = now();
            }
            if (Schema::hasColumn('inventory', 'last_updated')) {
                $inventoryData['last_updated'] = now();
            }

            DB::table('inventory')->insert($inventoryData);
        }

        return redirect()->route('admin.branch.management')->with('msg', 'added');
    }

    public function update(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'branch_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        $updates = [
            'branch_name' => $request->branch_name,
            'location' => $request->location,
        ];

        if (Schema::hasColumn('branches', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('branches')->where('id', $request->branch_id)->update($updates);

        return redirect()->route('admin.branch.management')->with('msg', 'updated');
    }

    public function archive($id)
    {
        $updates = ['archive_date' => now()];

        if (Schema::hasColumn('branches', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('branches')->where('id', $id)->update($updates);

        return redirect()->route('admin.branch.management')->with('msg', 'archived');
    }

    public function restore($id)
    {
        DB::table('branches')
            ->where('id', $id)
            ->whereNotNull('archive_date')
            ->update(['archive_date' => null]);

        return redirect()->route('admin.branch.archived')->with('msg', 'restored');
    }

    public function destroy($id)
    {
        DB::table('branches')
            ->where('id', $id)
            ->whereNotNull('archive_date')
            ->delete();

        return redirect()->route('admin.branch.archived')->with('msg', 'deleted');
    }
}