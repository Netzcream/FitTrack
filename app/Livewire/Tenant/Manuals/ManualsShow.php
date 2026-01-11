<?php

namespace App\Livewire\Tenant\Manuals;

use App\Models\Central\Manual;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class ManualsShow extends Component
{
    public Manual $manual;

    public function mount(Manual $manual)
    {
        // Verificar que el manual esté activo y publicado
        if (!$manual->is_active || !$manual->published_at || $manual->published_at->isFuture()) {
            abort(404);
        }

        $this->manual = $manual;
    }

    public function render()
    {
        return view('livewire.tenant.manuals.show');
    }
}
