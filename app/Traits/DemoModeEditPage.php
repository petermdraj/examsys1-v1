<?php

namespace App\Traits;

use Filament\Notifications\Notification;

/**
 * Apply this trait to any Filament EditRecord page class inside the Admin panel.
 * When DEMO_MODE=true the save action shows a persistent danger notification
 * and halts — the record is never written to the database.
 */
trait DemoModeEditPage
{
    protected function beforeSave(): void
    {
        if (config('quizora.demo_mode')) {
            Notification::make()
                ->title('Demo Mode — Editing disabled')
                ->body('This is a read-only demo. Changes cannot be saved.')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }
}
