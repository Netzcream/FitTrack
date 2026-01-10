<?php

namespace App\Livewire\Tenant\Exercises;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Exercise;
use Illuminate\Support\Str;

class QuickCreate extends Component
{
    public $name = '';
    public $category = '';
    public $level = 'intermediate';
    public $equipment = '';
    public $description = '';
    public $prefillName = '';

    public $categories = [
        'Piernas',
        'Pecho',
        'Espalda',
        'Brazos',
        'Hombros',
        'Core',
        'Cardio',
        'Full body',
    ];

    public $equipments = [
        'Peso corporal',
        'Barra',
        'Mancuernas',
        'Máquina',
        'Polea',
        'Banco',
        'Caja pliométrica',
        'Kettlebell',
        'Bandas elásticas',
    ];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:exercises,name',
            'category' => 'required|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'equipment' => 'required|string',
            'description' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
        'name.unique' => 'Ya existe un ejercicio con este nombre',
        'category.required' => 'Debes seleccionar una categoría',
        'equipment.required' => 'Debes seleccionar el equipo',
    ];

    #[On('prefill-exercise-name')]
    public function prefillName($name)
    {
        $this->name = $name;
    }

    public function save()
    {
        $this->validate();

        try {
            $exercise = Exercise::create([
                'uuid' => Str::uuid(),
                'name' => $this->name,
                'category' => $this->category,
                'level' => $this->level,
                'equipment' => $this->equipment,
                'description' => $this->description,
                'is_active' => true,
            ]);

            // Emitir evento para que el form lo capture y agregue el ejercicio
            $this->dispatch('exercise-created', exerciseId: $exercise->id);

            // Cerrar el modal con JavaScript
            $this->js("Flux.modal('quick-create-exercise-plan').close()");

            // Limpiar formulario
            $this->reset(['name', 'category', 'equipment', 'description']);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear ejercicio: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant.exercises.quick-create');
    }
}
