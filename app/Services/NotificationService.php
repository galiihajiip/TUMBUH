<?php

namespace App\Services;

use App\Models\Business;
use App\Models\GrowthNotification;

class NotificationService
{
    /**
     * Action-oriented notifications only. Identical unread titles are not
     * duplicated, which keeps repeated demo actions from spamming.
     */
    public function push(Business $business, string $type, string $title, ?string $body = null, ?string $actionLabel = null, ?string $actionUrl = null): GrowthNotification
    {
        $existing = $business->notifications()->unread()->where('title', $title)->first();
        if ($existing) {
            $existing->update(['body' => $body, 'created_at' => now()]);

            return $existing;
        }

        return $business->notifications()->create([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_label' => $actionLabel,
            'action_url' => $actionUrl,
        ]);
    }
}
