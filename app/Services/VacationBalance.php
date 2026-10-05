<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;

class VacationBalance
{
    // Explicit calendar-year demo policy; inclusive dates, unions, no carry-over.
    public function used(User $user, int $year): int
    {
        $start = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $end = $start->endOfYear()->startOfDay();
        $days = [];
        $requests = LeaveRequest::where('user_id', $user->id)->where('type', 'vacation')->where('status', 'approved')
            ->where('date_start', '<=', $end->toDateString())->where('date_end', '>=', $start->toDateString())->get();
        foreach ($requests as $request) {
            $from = CarbonImmutable::parse($request->date_start)->max($start);
            $to = CarbonImmutable::parse($request->date_end)->min($end);
            for ($day = $from; $day->lte($to); $day = $day->addDay()) {
                $days[$day->toDateString()] = true;
            }
        }

        return count($days);
    }

    public function remaining(User $user, int $year): int
    {
        return max(0, $user->vacation_days_per_year - $this->used($user, $year));
    }
}
