<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait InteractsWithLandingItems
{
    /**
     * Debe existir en el componente:
     * protected array $entityMeta = [
     *   'booklet' => [
     *      'array' => 'booklets',
     *      'model' => \App\Models\LandingBooklet::class,
     *      'fields'=> ['text','link','target','order','active'],
     *      'image' => ['prop' => 'booklet_image', 'collection' => 'cover', 'required_on_create' => true, 'max' => 20000],
     *   ],
     *   ...
     * ];
     */

    protected function meta(string $type): array
    {
        return $this->entityMeta[$type] ?? throw new \RuntimeException("Meta no definida para {$type}");
    }


    protected function propExists(string $prop): bool
    {
        return property_exists($this, $prop);
    }


    protected function refreshList(string $type): void
    {
        $m = $this->meta($type);
        $arrayProp = $m['array'];
        $this->{$arrayProp} = $this->q($type)->orderBy('order')->get()->map(function ($item) {
            return [
                'uuid'   => $item->uuid,
                // Campos comunes y opcionales:
                'title'  => $item->title ?? null,
                'text'   => $item->text ?? null,
                'link'   => $item->link ?? null,
                'target' => $item->target ?? '_self',
                'active' => (bool)($item->active ?? true),
                'order'  => (int)($item->order ?? 0),
                'image'  => $item->getFirstMediaUrl('cover', 'thumb'),
                'to_delete' => false,
            ];
        })->toArray();
    }
    public function newEntity(string $type): void
    {
        $m = $this->meta($type);
        $prefix = $type;

        // Construir lista de props a limpiar según fields declarados
        $toReset = ["{$prefix}_uuid"];

        foreach ($m['fields'] as $f) {
            $toReset[] = "{$prefix}_{$f}"; // ej: booklet_text, banner_link, card_title, etc.
        }

        // En algunos casos target/order/active podrían no estar en fields; ya los agregaste si están.
        // Filtrar a las que realmente existen como props públicas del componente
        $toReset = array_values(array_filter($toReset, fn($p) => $this->propExists($p)));

        if (!empty($toReset)) {
            $this->reset(...$toReset);
        }

        // Limpiar imagen si aplica
        if (!empty($m['image']['prop']) && $this->propExists($m['image']['prop'])) {
            $this->reset($m['image']['prop']);
        }

        // Modo edición OFF
        $this->setEditMode($type, false);
    }

    public function editEntity(string $type, string $uuid): void
    {
        $m = $this->meta($type);
        $arrayProp = $m['array'];
        $prefix = $type;

        $row = collect($this->{$arrayProp})->firstWhere('uuid', $uuid);
        if (!$row) return;

        // Siempre uuid si existe
        if ($this->propExists($prefix . '_uuid')) {
            $this->{$prefix . '_uuid'} = $row['uuid'];
        }

        // Asignar solo lo definido en fields, casteando tipos sensibles
        foreach ($m['fields'] as $f) {
            $prop = "{$prefix}_{$f}";
            if (!$this->propExists($prop)) {
                continue;
            }

            $val = $row[$f] ?? null;

            // Normalizaciones útiles
            if ($f === 'active') {
                $val = (bool) $val;
            } elseif ($f === 'order') {
                $val = (int) ($val ?? 0);
            } elseif ($f === 'target') {
                $val = $val ?: '_self';
            } else {
                // text/title/link u otros quedan tal cual o '' si null
                $val = $val ?? '';
            }

            $this->{$prop} = $val;
        }

        $this->setEditMode($type, true);
    }

    protected function setEditMode(string $type, bool $value): void
    {
        $prop = $type . '_edit_mode'; // ej. booklet_edit_mode
        if (property_exists($this, $prop)) {
            $this->{$prop} = $value;
        }
    }


    public function saveEntity(string $type): void
    {
        $m = $this->meta($type);
        $prefix = $type;
        $editModeProp = "{$prefix}_edit_mode";
        $uuidProp = "{$prefix}_uuid";

        // Validación dinámica
        $rules = [
            "{$prefix}_link"   => 'nullable|url',
            "{$prefix}_target" => 'required|in:_self,_blank',
            "{$prefix}_order"  => 'nullable|integer|min:0',
            "{$prefix}_active" => 'boolean',
        ];
        if (in_array('text', $m['fields'])) {
            $rules["{$prefix}_text"] = 'nullable|string|max:255';
        }
        if (in_array('title', $m['fields'])) {
            // cards requieren title y text en tu implementación
            $rules["{$prefix}_title"] = 'required|string|max:50';
            $rules["{$prefix}_text"]  = 'required|string|max:255';
        }

        // imagen
        if (!empty($m['image']['prop'])) {
            $imgRule = 'nullable|image|mimes:jpg,jpeg,png,webp|max:' . $m['image']['max'];
            if (!$this->{$editModeProp} && !empty($m['image']['required_on_create'])) {
                $imgRule = 'required|' . $imgRule;
            }
            $rules[$m['image']['prop']] = $imgRule;
        }

        $this->validate($rules);

        // Upsert
        if ($this->{$editModeProp} && $this->{$uuidProp}) {
            $model = $m['model']::where('uuid', $this->{$uuidProp})->first();
            if ($model) {
                $payload = $this->payloadFromForm($type);
                $model->update($payload);
                $this->syncImageIfAny($type, $model);
            }
        } else {
            $payload = $this->payloadFromForm($type);
            $payload['uuid'] = Str::uuid()->toString();

            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $m['model']::create($payload);
            $this->syncImageIfAny($type, $model);
        }

        $this->refreshList($type);
        $this->reorderEntities($type);
        $this->newEntity($type); // limpiar form

        if (!empty($m['image']['prop']) && $this->propExists($m['image']['prop'])) {
            $this->reset($m['image']['prop']);
        }

        // Ajustar orden total mostrado
        $orderProp = "{$prefix}_order";
        $arrayProp = $m['array'];
        if ($this->propExists($orderProp)) {
            $this->{$orderProp} = count($this->{$arrayProp});
        }
    }

    protected function payloadFromForm(string $type): array
    {
        $m = $this->meta($type);
        $prefix = $type;
        $data = [];
        foreach ($m['fields'] as $f) {
            $key = "{$prefix}_{$f}";
            $data[$f] = $this->{$key} ?? null;
        }
        return $data;
    }

    protected function syncImageIfAny(string $type, $model): void
    {
        $m = $this->meta($type);
        if (empty($m['image']['prop'])) return;

        $prop = $m['image']['prop'];
        if ($this->{$prop} instanceof TemporaryUploadedFile) {
            $collection = $m['image']['collection'] ?? 'cover';
            $model->clearMediaCollection($collection);
            $model->addMedia($this->{$prop})->toMediaCollection($collection);
        }
    }

    public function deleteEntity(string $type, string $uuid): void
    {
        $m = $this->meta($type);
        $arrayProp = $m['array'];

        $index = array_search($uuid, array_column($this->{$arrayProp}, 'uuid'));
        if ($index === false) return;

        $inDb = $m['model']::where('uuid', $uuid)->exists();
        if ($inDb) {
            $this->{$arrayProp}[$index]['to_delete'] = true;
        } else {
            array_splice($this->{$arrayProp}, $index, 1);
        }
    }

    public function restoreEntity(string $type, string $uuid): void
    {
        $m = $this->meta($type);
        $arrayProp = $m['array'];

        $index = array_search($uuid, array_column($this->{$arrayProp}, 'uuid'));
        if ($index !== false) {
            $this->{$arrayProp}[$index]['to_delete'] = false;
        }
    }

    public function moveEntityUp(string $type, string $uuid): void
    {
        $items = $this->q($type)->orderBy('order')->get();
        $index = $items->search(fn($i) => $i->uuid === $uuid);
        if ($index === false || $index === 0) return;

        $current = $items[$index];
        $above   = $items[$index - 1];

        [$current->order, $above->order] = [$above->order, $current->order];
        $current->save();
        $above->save();

        $this->mount($this->section);
    }

    public function moveEntityDown(string $type, string $uuid): void
    {
        $items = $this->q($type)->orderBy('order')->get();
        $index = $items->search(fn($i) => $i->uuid === $uuid);
        if ($index === false || $index === $items->count() - 1) return;

        $current = $items[$index];
        $below   = $items[$index + 1];

        [$current->order, $below->order] = [$below->order, $current->order];
        $current->save();
        $below->save();

        $this->mount($this->section);
    }

    public function reorderEntities(string $type): void
    {
        $items = $this->q($type)->whereNull('deleted_at')->orderBy('order')->get();
        foreach ($items as $index => $item) {
            if ((int)$item->order !== $index) {
                $item->update(['order' => $index]);
            }
        }
        $this->mount($this->section);
    }

    protected function syncEntitiesOnSave(string $type): void
    {
        // llamado desde save() general del prestador
        $m = $this->meta($type);
        $arrayProp = $m['array'];

        foreach ($this->{$arrayProp} as $row) {
            if (!empty($row['to_delete'])) {
                $model = $m['model']::where('uuid', $row['uuid'])->first();
                if ($model) {
                    $model->clearMediaCollection($m['image']['collection'] ?? 'cover');
                    $model->delete();
                }
                continue;
            }

            $payload = [];
            foreach ($m['fields'] as $f) $payload[$f] = $row[$f] ?? null;
            $m['model']::updateOrCreate(
                ['uuid' => $row['uuid']],
                $payload
            );
        }
    }
}
