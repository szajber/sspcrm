<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\ProtocolFireDamper;
use App\Models\FireDamper;
use App\Models\FireDamperType;
use Livewire\Component;

class ProtocolFireDampersManager extends Component
{
    public Protocol $protocol;

    // Form properties
    public $editingId = null;
    public $showModal = false;
    public $type_id;
    public $custom_type;
    public $location;
    public $manufacturer;

    public $availableTypes = [];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function render()
    {
        // Pobieramy dostępne typy
        $this->availableTypes = FireDamperType::orderBy('name')->get();

        // Pobieramy klapy posortowane według kolejności z inwentarza
        $dampers = ProtocolFireDamper::where('protocol_id', $this->protocol->id)
            ->leftJoin('fire_dampers', 'protocol_fire_dampers.fire_damper_id', '=', 'fire_dampers.id')
            ->select('protocol_fire_dampers.*')
            ->orderBy('fire_dampers.sort_order')
            ->orderBy('protocol_fire_dampers.id')
            ->get();

        return view('livewire.protocol-fire-dampers-manager', [
            'dampers' => $dampers
        ]);
    }

    public function moveUp($id)
    {
        $current = ProtocolFireDamper::find($id);
        if (!$current || !$current->fire_damper_id) return;

        $currentInventory = FireDamper::find($current->fire_damper_id);
        if (!$currentInventory) return;

        $previousInventory = FireDamper::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '<', $currentInventory->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousInventory) {
            $tempOrder = $currentInventory->sort_order;
            $currentInventory->update(['sort_order' => $previousInventory->sort_order]);
            $previousInventory->update(['sort_order' => $tempOrder]);
        }
    }

    public function moveDown($id)
    {
        $current = ProtocolFireDamper::find($id);
        if (!$current || !$current->fire_damper_id) return;

        $currentInventory = FireDamper::find($current->fire_damper_id);
        if (!$currentInventory) return;

        $nextInventory = FireDamper::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextInventory) {
            $tempOrder = $currentInventory->sort_order;
            $currentInventory->update(['sort_order' => $nextInventory->sort_order]);
            $nextInventory->update(['sort_order' => $tempOrder]);
        }
    }

    public function moveUp10($id)
    {
        $current = ProtocolFireDamper::find($id);
        if (!$current || !$current->fire_damper_id) return;

        $currentInventory = FireDamper::find($current->fire_damper_id);
        if (!$currentInventory) return;

        $previousInventories = FireDamper::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '<', $currentInventory->sort_order)
            ->orderBy('sort_order', 'desc')
            ->take(10)
            ->get();

        if ($previousInventories->isEmpty()) return;

        $targetInventory = $previousInventories->last();

        $range = FireDamper::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>=', $targetInventory->sort_order)
            ->where('sort_order', '<=', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($range->count() > 1) {
            $ids = $range->pluck('id')->toArray();
            $orders = $range->pluck('sort_order')->toArray();
            $currentId = array_pop($ids);
            array_unshift($ids, $currentId);
            foreach ($ids as $index => $id) {
                FireDamper::where('id', $id)->update(['sort_order' => $orders[$index]]);
            }
        }
    }

    public function moveDown10($id)
    {
        $current = ProtocolFireDamper::find($id);
        if (!$current || !$current->fire_damper_id) return;

        $currentInventory = FireDamper::find($current->fire_damper_id);
        if (!$currentInventory) return;

        $nextInventories = FireDamper::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->take(10)
            ->get();

        if ($nextInventories->isEmpty()) return;

        $targetInventory = $nextInventories->last();

        $range = FireDamper::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>=', $currentInventory->sort_order)
            ->where('sort_order', '<=', $targetInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($range->count() > 1) {
            $ids = $range->pluck('id')->toArray();
            $orders = $range->pluck('sort_order')->toArray();
            $currentId = array_shift($ids);
            array_push($ids, $currentId);
            foreach ($ids as $index => $id) {
                FireDamper::where('id', $id)->update(['sort_order' => $orders[$index]]);
            }
        }
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
        $this->type_id = null;
        $this->custom_type = '';
        $this->location = '';
        $this->manufacturer = '';
    }

    public function edit($id)
    {
        $damper = ProtocolFireDamper::find($id);
        if ($damper) {
            $this->editingId = $id;
            $this->location = $damper->location;
            $this->manufacturer = $damper->manufacturer;

            // Próba ustalenia ID typu
            $this->type_id = null;
            $this->custom_type = '';

            // 1. Sprawdź w powiązanym inwentarzu
            if ($damper->fire_damper_id) {
                $inventory = FireDamper::find($damper->fire_damper_id);
                if ($inventory && $inventory->type_id) {
                    $this->type_id = $inventory->type_id;
                }
            }

            // 2. Jeśli nie znaleziono ID, spróbuj znaleźć po nazwie w słowniku
            if (!$this->type_id && $damper->type_name) {
                $existingType = FireDamperType::where('name', $damper->type_name)->first();
                if ($existingType) {
                    $this->type_id = $existingType->id;
                } else {
                    $this->custom_type = $damper->type_name;
                }
            }

            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'type_id' => 'nullable|exists:fire_damper_types,id',
            'custom_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
        ]);

        // Automatyczne dodawanie nowego typu do słownika
        if (empty($this->type_id) && !empty($this->custom_type)) {
            $existingType = FireDamperType::where('name', $this->custom_type)->first();

            if ($existingType) {
                $this->type_id = $existingType->id;
            } else {
                $newType = FireDamperType::create([
                    'name' => $this->custom_type,
                    'description' => 'Dodano automatycznie podczas tworzenia protokołu.'
                ]);
                $this->type_id = $newType->id;
                $this->availableTypes = FireDamperType::orderBy('name')->get();
            }
        }

        $typeName = '';
        if ($this->type_id) {
            $typeObj = FireDamperType::find($this->type_id);
            $typeName = $typeObj ? $typeObj->name : $this->custom_type;
        } else {
            $typeName = $this->custom_type;
        }

        // Preferujemy ID w inwentarzu
        $inventoryCustomType = $this->type_id ? null : $this->custom_type;
        $inventoryTypeId = $this->type_id ?: null;

        if ($this->editingId) {
            // Edycja
            $damper = ProtocolFireDamper::find($this->editingId);
            $damper->update([
                'type_name' => $typeName,
                'location' => $this->location,
                'manufacturer' => $this->manufacturer,
            ]);

            if ($damper->fire_damper_id) {
                $inventory = FireDamper::find($damper->fire_damper_id);
                if ($inventory) {
                    $inventory->update([
                        'type_id' => $inventoryTypeId,
                        'custom_type' => $inventoryCustomType,
                        'location' => $this->location,
                        'manufacturer' => $this->manufacturer,
                    ]);
                }
            }
        } else {
            // Dodawanie
            $maxOrder = FireDamper::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;

            $inventory = FireDamper::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'type_id' => $inventoryTypeId,
                'custom_type' => $inventoryCustomType,
                'location' => $this->location,
                'manufacturer' => $this->manufacturer,
                'sort_order' => $maxOrder + 1,
            ]);

            $this->protocol->fireDampers()->create([
                'fire_damper_id' => $inventory->id,
                'type_name' => $typeName,
                'location' => $this->location,
                'manufacturer' => $this->manufacturer,
                'result' => 'positive', // Domyślnie pozytywny
            ]);
        }

        $this->closeModal();
    }

    public function clone($id)
    {
        $original = ProtocolFireDamper::find($id);
        if ($original) {
            $new = null;

            if ($original->fire_damper_id) {
                $inventoryOrig = FireDamper::find($original->fire_damper_id);
                if ($inventoryOrig) {
                    $maxOrder = FireDamper::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    $new = $original->replicate();
                    $new->fire_damper_id = $inventoryNew->id;
                    $new->save();
                }
            } else {
                 $new = $original->replicate();
                 $new->save();
            }

            if ($new) {
                $this->edit($new->id);
            }
        }
    }

    public function delete($id)
    {
        $damper = ProtocolFireDamper::find($id);
        if ($damper) {
            $damper->delete();
            if ($damper->fire_damper_id) {
                $inventory = FireDamper::find($damper->fire_damper_id);
                if ($inventory) {
                    $inventory->update(['is_active' => false]);
                }
            }
        }
    }
}
