<?php

namespace App\Filament\Creator\Resources\QuestionBankResource\Pages;

use App\Exceptions\PlanLimitExceededException;
use App\Filament\Creator\Resources\QuestionBankResource;
use App\Services\Quiz\PlanLimitService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBankQuestion extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;

    public function mount(): void
    {
        try {
            app(PlanLimitService::class)->assertCanAddBankQuestion(auth()->user());
        } catch (PlanLimitExceededException $e) {
            Notification::make()->title(__('creator.notif_bank_limit_reached'))->body($e->getMessage())->danger()->persistent()->send();
            $this->redirect(QuestionBankResource::getUrl('index'));
            return;
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['quiz_id']    = null;          // bank question — no quiz
        $data['creator_id'] = auth()->id();  // owner
        $data['sort_order'] = 0;
        return $data;
    }
}
