<?php

namespace App\Concerns;

use Inertia\Inertia;

trait FlashesToastMessages
{
    protected function flashToast(string $type, string $message): void
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);
    }
}
