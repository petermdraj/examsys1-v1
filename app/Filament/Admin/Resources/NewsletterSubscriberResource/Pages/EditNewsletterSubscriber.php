<?php

namespace App\Filament\Admin\Resources\NewsletterSubscriberResource\Pages;

use App\Filament\Admin\Resources\NewsletterSubscriberResource;
use App\Traits\DemoModeEditPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterSubscriber extends EditRecord
{
    use DemoModeEditPage;
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
