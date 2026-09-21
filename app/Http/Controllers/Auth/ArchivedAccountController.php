<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchivedAccountController extends Controller
{
    // Ipakita ang archived accounts list
    public function index()
    {
        // 1. Auto-Delete Policy (Lampas 30 days na naka-archive)
        DB::table('users')
            ->where('is_archived', 1)
            ->where('archive_date', '<', Carbon::now()->subDays(30))
            ->delete();

        // 2. Kunin ang mga nakarchive
        $archived = DB::table('users')
            ->where('is_archived', 1)
            ->orderBy('archive_date', 'DESC')
            ->get();

        return view('admin.archived_accounts', compact('archived'));
    }

    // Restore Logic
    public function restore($id)
    {
        DB::table('users')
            ->where('id', $id)
            ->update([
                'is_archived' => 0,
                'archive_date' => null
            ]);

        return redirect()->route('admin.archived.index')->with('msg', 'restored');
    }

    // Permanent Delete Logic
    public function destroy($id)
    {
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.archived.index')->with('msg', 'deleted');
    }
}