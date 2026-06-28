<?php

namespace App\Traits;

/**
 * Demo-mode restrictions are handled at action level:
 *  - Deletes → Tables\Actions\DeleteAction::configureUsing in AppServiceProvider
 *  - Saves   → DemoModeEditPage trait on every EditRecord page
 *
 * This trait is kept as a no-op placeholder so all resource classes that
 * already `use RestrictInDemoMode` continue to compile without changes.
 */
trait RestrictInDemoMode
{
    // intentionally empty — restrictions are applied globally via AppServiceProvider
}
