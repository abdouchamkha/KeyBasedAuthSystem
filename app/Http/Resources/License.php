<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class License extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        $total_subscription_duration = ($this->subscription_duration ?? 0) + ($this->extra_time ?? 0);
        $time_left = 'Expired'; // Default value

        if ($this->started_at) {
            // License has started
            $started_at = $this->started_at;

            if ($this->end_at) {
                $end_at = $this->end_at;
            } else {
                $end_at = $started_at->copy()->addHours($total_subscription_duration);
            }

            $now = Carbon::now();

            $diffInSeconds = $end_at->timestamp - $now->timestamp;

            if ($diffInSeconds > 0) {
                // License is active
                $days = floor($diffInSeconds / 86400);
                $diffInSeconds -= $days * 86400;

                $hours = floor($diffInSeconds / 3600);
                $diffInSeconds -= $hours * 3600;

                $minutes = floor($diffInSeconds / 60);
                $seconds = $diffInSeconds - $minutes * 60;

                // Build the time_left string
                $time_parts = [];

                if ($days > 0) {
                    $time_parts[] = $days . 'd';
                }

                if ($hours > 0 || !empty($time_parts)) {
                    $time_parts[] = $hours . 'h';
                }

                if ($minutes > 0 || !empty($time_parts)) {
                    $time_parts[] = $minutes . 'm';
                }

                if ($seconds > 0 || !empty($time_parts)) {
                    $time_parts[] = $seconds . 's';
                }

                $time_left = implode(' ', $time_parts);

            } else {
                // License has expired
                $time_left = 'Expired';
            }
        } else {
            // License hasn't started yet
            $time_left = 'Not started';
        }

        // Formatting extra_time
        $extra_time_formatted = $this->formatHoursToDaysHoursMinutes($this->extra_time);

        // Formatting subscription_duration
        $subscription_duration_formatted = $this->formatHoursToDaysHoursMinutes($this->subscription_duration);

        return [
            'id' => $this->id,
            'app' => new Application($this->whenLoaded('app')),
            'product' => new Product($this->whenLoaded('product')),
            'sessions' => $this->sessions,
            'hwid' => $this->hwid,
            'user' => new User($this->whenLoaded('user')),
            'license_value' => $this->license_value,
            'hwid_lock' => $this->hwid_lock ? 'Enabled' : 'Disabled',
            'uuid_value' => $this->uuid_value,
            'frozen_at' => $this->frozen_at ? $this->frozen_at->toDateTimeString() : 'Not frozen',
            'banned_at' => $this->banned_at ? $this->banned_at->toDateTimeString() : 'Not banned',
            'started_at' => $this->started_at ? $this->started_at->toDateTimeString() : 'Not started',
            'end_at' => $this->end_at ? $this->end_at->toDateTimeString() : 'N/A',
            'extra_time' => $extra_time_formatted,
            'subscription_duration' => $subscription_duration_formatted,
            'created_at' => $this->created_at->toDateTimeString(),
            'time_left' => $time_left,
        ];
    }

    /**
     * Format hours into 'Xd Yh Zm' format.
     *
     * @param int|null $hours
     * @return string
     */
    private function formatHoursToDaysHoursMinutes(?int $hours): string
    {
        if ($hours === null || $hours <= 0) {
            return 'N/A';
        }

        // Since hours are integers, minutes will be zero
        $totalMinutes = $hours * 60;

        $days = intdiv($totalMinutes, 1440); // 1440 minutes in a day
        $totalMinutes -= $days * 1440;

        $remainingHours = intdiv($totalMinutes, 60);
        $totalMinutes -= $remainingHours * 60;

        $minutes = $totalMinutes; // Will always be zero in this case

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'd';
        }

        if ($remainingHours > 0 && !empty($parts)) {
            $parts[] = $remainingHours . 'h';
        }

        if ($minutes > 0 && !empty($parts)) {
            $parts[] = $minutes . 'm';
        }

        return implode(' ', $parts);
    }
}
