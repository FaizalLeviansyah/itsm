<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;

class SlaCalculator
{
    protected int $workStartHour = 8;
    protected int $workEndHour = 17;

    /**
     * Calculate the due date based on SLA hours, excluding weekends and holidays.
     */
    public function calculateDueDate(Carbon $startDate, int $slaHours): Carbon
    {
        $remainingMinutes = $slaHours * 60;
        $current = $startDate->copy();

        // If starting outside work hours, move to next work start
        if ($current->hour >= $this->workEndHour || $current->hour < $this->workStartHour) {
            $current = $this->nextWorkStart($current);
        }

        while ($remainingMinutes > 0) {
            if ($this->isWorkingDay($current)) {
                $endOfDay = $current->copy()->setHour($this->workEndHour)->setMinute(0)->setSecond(0);
                $minutesLeftToday = $current->diffInMinutes($endOfDay);

                if ($remainingMinutes <= $minutesLeftToday) {
                    $current->addMinutes($remainingMinutes);
                    $remainingMinutes = 0;
                } else {
                    $remainingMinutes -= $minutesLeftToday;
                    $current = $this->nextWorkStart($current->addDay());
                }
            } else {
                $current = $this->nextWorkStart($current->addDay());
            }
        }

        return $current;
    }

    /**
     * Calculate elapsed business hours between two dates.
     */
    public function calculateElapsedBusinessHours(Carbon $start, Carbon $end): float
    {
        $totalMinutes = 0;
        $current = $start->copy();

        // Move to work start if needed
        if ($current->hour < $this->workStartHour) {
            $current->setHour($this->workStartHour)->setMinute(0);
        }
        if ($current->hour >= $this->workEndHour) {
            $current = $this->nextWorkStart($current->addDay());
        }

        while ($current->lt($end)) {
            if ($this->isWorkingDay($current)) {
                $dayEnd = $current->copy()->setHour($this->workEndHour)->setMinute(0);
                $effectiveEnd = $end->lt($dayEnd) ? $end : $dayEnd;

                if ($current->lt($effectiveEnd)) {
                    $totalMinutes += $current->diffInMinutes($effectiveEnd);
                }
            }
            $current = $this->nextWorkStart($current->addDay());
        }

        return round($totalMinutes / 60, 1);
    }

    protected function isWorkingDay(Carbon $date): bool
    {
        // Weekend check (Saturday=6, Sunday=0)
        if ($date->isWeekend()) {
            return false;
        }

        // Holiday check
        $isHoliday = Holiday::where(function ($q) use ($date) {
            $q->where('date', $date->toDateString());
            $q->orWhere(function ($q2) use ($date) {
                $q2->where('is_recurring', true)
                   ->whereMonth('date', $date->month)
                   ->whereDay('date', $date->day);
            });
        })->exists();

        return !$isHoliday;
    }

    protected function nextWorkStart(Carbon $date): Carbon
    {
        $next = $date->copy()->setHour($this->workStartHour)->setMinute(0)->setSecond(0);

        while (!$this->isWorkingDay($next)) {
            $next->addDay();
        }

        return $next;
    }
}
