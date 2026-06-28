<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Livewire\Component;

class NewsletterSubscribe extends Component
{
    public string $email  = '';
    public string $status = ''; // success | duplicate | error

    public string $source = 'footer';

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        $exists = NewsletterSubscriber::where('email', strtolower(trim($this->email)))->first();

        if ($exists) {
            if (! $exists->is_active) {
                // Re-subscribe
                $exists->update(['is_active' => true, 'unsubscribed_at' => null, 'subscribed_at' => now()]);
                $this->status = 'success';
            } else {
                $this->status = 'duplicate';
            }
            $this->email = '';
            return;
        }

        NewsletterSubscriber::create([
            'email'  => strtolower(trim($this->email)),
            'source' => $this->source,
        ]);

        $this->email  = '';
        $this->status = 'success';
    }

    public function render()
    {
        return view('livewire.newsletter-subscribe');
    }
}
