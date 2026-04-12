<?php

namespace App\Http\Controllers;

use App\Services\BalineseCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalineseCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) ($request->get('year', now()->year));
        $month = (int) ($request->get('month', now()->month));

        if ($month < 1) {
            $month = 1;
        }
        if ($month > 12) {
            $month = 12;
        }

        // Today's info
        $today = BalineseCalendarService::today();
        $todayInfo = $today->getFullInfo();

        // Calendar info for the requested month
        $calMonth = BalineseCalendarService::forDate($year, $month, 1);
        $monthGrid = $calMonth->getMonthGrid($year, $month);
        $monthStartInfo = $calMonth->getFullInfo();

        // Navigation
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear = $month === 1 ? $year - 1 : $year;
        $nextMonth = $month === 12 ? 1 : $month + 1;
        $nextYear = $month === 12 ? $year + 1 : $year;

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');

        // Full wuku list
        $allWuku = BalineseCalendarService::getAllWuku();

        return view('kalender-bali.index', [
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'monthGrid' => $monthGrid,
            'todayInfo' => $todayInfo,
            'monthStartInfo' => $monthStartInfo,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
            'allWuku' => $allWuku,
        ]);
    }
}
