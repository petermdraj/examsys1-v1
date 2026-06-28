<?php

namespace App\Livewire;

use App\Models\QuizFavourite;
use Livewire\Component;

class ToggleFavourite extends Component
{
    public string $quizId;
    public bool   $isFavourited = false;

    public function mount(string $quizId): void
    {
        $this->quizId = $quizId;
        $this->isFavourited = auth()->check()
            && QuizFavourite::where('user_id', auth()->id())
                             ->where('quiz_id', $quizId)
                             ->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $existing = QuizFavourite::where('user_id', auth()->id())
                                  ->where('quiz_id', $this->quizId)
                                  ->first();

        if ($existing) {
            $existing->delete();
            $this->isFavourited = false;
        } else {
            QuizFavourite::create([
                'user_id' => auth()->id(),
                'quiz_id' => $this->quizId,
            ]);
            $this->isFavourited = true;
        }
    }

    public function render()
    {
        return view('livewire.toggle-favourite');
    }
}
