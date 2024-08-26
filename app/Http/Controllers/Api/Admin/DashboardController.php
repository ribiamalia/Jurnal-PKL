<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Activity;
use App\Models\Visits;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $period = $request->query('period', 'daily'); // default to daily

        // Function to get data grouped by period
        $getDataByPeriod = function ($model, $user = null) use ($period, $startDate, $endDate) {
            $query = $model::query();
            
            // Filter by user if not admin
            if ($user && !$user->hasRole('admin')) {
                $query->where('user_id', $user->id);
            }

            // Filter by date range if provided
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // Group by the selected period
            switch ($period) {
                case 'daily':
                    return $query->selectRaw('DATE(date) as period, COUNT(*) as count')
                                 ->groupBy('period')
                                 ->get();
                case 'weekly':
                    return $query->selectRaw('YEARWEEK(date) as period, COUNT(*) as count')
                                 ->groupBy('period')
                                 ->get();
                case 'monthly':
                    return $query->selectRaw('DATE_FORMAT(date, "%Y-%m") as period, COUNT(*) as count')
                                 ->groupBy('period')
                                 ->get();
                case 'yearly':
                    return $query->selectRaw('YEAR(date) as period, COUNT(*) as count')
                                 ->groupBy('period')
                                 ->get();
                default:
                    throw new \InvalidArgumentException('Invalid period selected');
            }
        };

        // Collecting data based on the role
        if ($user->hasRole('admin')) {
            $data = [
                'attendance' => Attendance::count(),
                'activity' => Activity::count(),
                'visit' => Visits::count(),
                'attendance_breakdown' => $getDataByPeriod(Attendance::class),
                'activity_breakdown' => $getDataByPeriod(Activity::class),
               
            ];
        } else {
            $data = [
                'attendance' => Attendance::where('user_id', $user->id)->count(),
                'activity' => Activity::where('user_id', $user->id)->count(),
                'visit' => Visits::where('user_id', $user->id)->count(),
                'attendance_breakdown' => $getDataByPeriod(Attendance::class, $user),
                'activity_breakdown' => $getDataByPeriod(Activity::class, $user),
               
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'List Data on Dashboard',
            'data' => $data,
        ]);
    }
}
