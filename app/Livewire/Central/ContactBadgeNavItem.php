<?php

namespace App\Livewire\Central;

use App\Models\Central\Contact;
use Livewire\Component;

class ContactBadgeNavItem extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->updateCount();
    }

    private function updateCount(): void
    {
        $this->unreadCount = Contact::query()->unread()->count();
    }

    #[\Livewire\Attributes\On('contact-unread-count-updated')]
    public function refreshUnreadCount(): void
    {
        $this->updateCount();
    }

    public function render()
    {
        $this->updateCount();

        return view('livewire.central.contact-badge-nav-item');
    }
}
