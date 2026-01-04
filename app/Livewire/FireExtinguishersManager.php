<?php

namespace App\Livewire;

use App\Models\ClientObject;
use App\Models\FireExtinguisher;
use App\Models\FireExtinguisherType;
use Livewire\Component;

class FireExtinguishersManager extends Component
{
    public ClientObject $object;
    public $types;

    // Form properties
    public $editingId = null;
    public $showModal = false;
    public $type_id;
    public $custom_type;
    public $location;

    public function mount(ClientObject $object)
    {
        $this->object = $object;
        $this->types = FireExtinguisherType::orderBy('name')->get();
    }

    public function render()
    {
        $extinguishers = FireExtinguisher::where('client_object_id', $this->object->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.fire-extinguishers-manager', [
            'extinguishers' => $extinguishers
        ]);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->type_id = '';
        $this->custom_type = '';
        $this->location = '';
    }

    public function edit($id)
    {
        $extinguisher = FireExtinguisher::find($id);
        if ($extinguisher) {
            $this->editingId = $id;
            $this->type_id = $extinguisher->type_id;
            $this->custom_type = $extinguisher->custom_type;
            $this->location = $extinguisher->location;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'type_id' => 'nullable|exists:fire_extinguisher_types,id',
            'custom_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        if (empty($this->type_id) && empty($this->custom_type)) {
            $this->addError('type_id', 'Wybierz typ z listy lub wpisz własny.');
            return;
        }

        if ($this->editingId) {
            $extinguisher = FireExtinguisher::find($this->editingId);
            $extinguisher->update([
                'type_id' => $this->type_id ?: null,
                'custom_type' => $this->custom_type,
                'location' => $this->location,
            ]);
        } else {
            // Get max sort order
            $maxOrder = FireExtinguisher::where('client_object_id', $this->object->id)
                ->max('sort_order') ?? 0;

            FireExtinguisher::create([
                'client_object_id' => $this->object->id,
                'type_id' => $this->type_id ?: null,
                'custom_type' => $this->custom_type,
                'location' => $this->location,
                'sort_order' => $maxOrder + 1,
            ]);
        }

        $this->closeModal();
    }

    public function clone($id)
    {
        $original = FireExtinguisher::find($id);
        if ($original) {
            $maxOrder = FireExtinguisher::where('client_object_id', $this->object->id)
                ->max('sort_order') ?? 0;

            $new = $original->replicate();
            $new->sort_order = $maxOrder + 1;
            $new->save();
        }
    }

    public function delete($id)
    {
        $extinguisher = FireExtinguisher::find($id);
        if ($extinguisher) {
            $extinguisher->update(['is_active' => false]);
        }
    }

    public function updateOrder($items)
    {
        foreach ($items as $item) {
            FireExtinguisher::where('id', $item['value'])
                ->update(['sort_order' => $item['order']]);
        }
    }
}
