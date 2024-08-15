<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // Mendapatkan tahun yang diinginkan, default adalah tahun ini
        $year = $request->input('year', now()->year);

        // Menghitung jumlah absensi per bulan untuk tahun tertentu
        $attendanceStats = Attendance::select(
            DB::raw('MONTH(date) as month'), 
            DB::raw('COUNT(*) as total_attendances')
        )
        ->whereYear('date', $year)
        ->groupBy(DB::raw('MONTH(date)'))
        ->orderBy('month')
        ->get();

        // Membuat data dalam format yang lebih informatif
        $formattedStats = $attendanceStats->map(function ($item) {
            return [
                'month' => Carbon::createFromFormat('m', $item->month)->format('F'),
                'total_attendances' => $item->total_attendances
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Attendance statistics retrieved successfully.',
            'data' => $formattedStats,
        ]);
    }
}
