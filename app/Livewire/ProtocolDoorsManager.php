<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\ProtocolDoor;
use App\Models\Door;
use App\Models\DoorResistanceClass;
use Livewire\Component;

class ProtocolDoorsManager extends Component
{
    public Protocol $protocol;

    // Form properties
    public $editingId = null;
    public $showModal = false;
    public $resistance_class_id;
    public $custom_resistance_class;
    public $location;

    public $availableClasses = [];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function render()
    {
        // Pobieramy dostępne klasy
        $this->availableClasses = DoorResistanceClass::orderBy('name')->get();

        // Pobieramy drzwi posortowane według kolejności z inwentarza
        $doors = ProtocolDoor::where('protocol_id', $this->protocol->id)
            ->leftJoin('doors', 'protocol_doors.door_id', '=', 'doors.id')
            ->select('protocol_doors.*')
            ->orderBy('doors.sort_order')
            ->orderBy('protocol_doors.id')
            ->get();

        return view('livewire.protocol-doors-manager', [
            'doors' => $doors
        ]);
    }

    public function moveUp($id)
    {
        $current = ProtocolDoor::find($id);
        if (!$current || !$current->door_id) return;

        $currentInventory = Door::find($current->door_id);
        if (!$currentInventory) return;

        $previousInventory = Door::where('client_object_id', $currentInventory->client_object_id)
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
        $current = ProtocolDoor::find($id);
        if (!$current || !$current->door_id) return;

        $currentInventory = Door::find($current->door_id);
        if (!$currentInventory) return;

        $nextInventory = Door::where('client_object_id', $currentInventory->client_object_id)
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
        $current = ProtocolDoor::find($id);
        if (!$current || !$current->door_id) return;

        $currentInventory = Door::find($current->door_id);
        if (!$currentInventory) return;

        $previousInventories = Door::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '<', $currentInventory->sort_order)
            ->orderBy('sort_order', 'desc')
            ->take(10)
            ->get();

        if ($previousInventories->isEmpty()) return;

        $targetInventory = $previousInventories->last();

        $range = Door::where('client_object_id', $currentInventory->client_object_id)
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
                Door::where('id', $id)->update(['sort_order' => $orders[$index]]);
            }
        }
    }

    public function moveDown10($id)
    {
        $current = ProtocolDoor::find($id);
        if (!$current || !$current->door_id) return;

        $currentInventory = Door::find($current->door_id);
        if (!$currentInventory) return;

        $nextInventories = Door::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->take(10)
            ->get();

        if ($nextInventories->isEmpty()) return;

        $targetInventory = $nextInventories->last();

        $range = Door::where('client_object_id', $currentInventory->client_object_id)
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
                Door::where('id', $id)->update(['sort_order' => $orders[$index]]);
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
        $this->resistance_class_id = null;
        $this->custom_resistance_class = '';
        $this->location = '';
    }

    public function edit($id)
    {
        $door = ProtocolDoor::find($id);
        if ($door) {
            $this->editingId = $id;
            $this->location = $door->location;

            // Próba ustalenia ID klasy
            $this->resistance_class_id = null;
            $this->custom_resistance_class = '';

            // 1. Sprawdź w powiązanym inwentarzu
            if ($door->door_id) {
                $inventory = Door::find($door->door_id);
                if ($inventory && $inventory->resistance_class_id) {
                    $this->resistance_class_id = $inventory->resistance_class_id;
                }
            }

            // 2. Jeśli nie znaleziono ID, spróbuj znaleźć po nazwie w słowniku
            if (!$this->resistance_class_id && $door->resistance_class) {
                $existingClass = DoorResistanceClass::where('name', $door->resistance_class)->first();
                if ($existingClass) {
                    $this->resistance_class_id = $existingClass->id;
                } else {
                    $this->custom_resistance_class = $door->resistance_class;
                }
            }

            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'resistance_class_id' => 'nullable|exists:door_resistance_classes,id',
            'custom_resistance_class' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // Automatyczne dodawanie nowego typu do słownika
        if (empty($this->resistance_class_id) && !empty($this->custom_resistance_class)) {
            // Sprawdź czy taki typ już istnieje (case-insensitive)
            $existingClass = DoorResistanceClass::where('name', $this->custom_resistance_class)->first();

            if ($existingClass) {
                $this->resistance_class_id = $existingClass->id;
            } else {
                // Utwórz nowy typ
                $newClass = DoorResistanceClass::create([
                    'name' => $this->custom_resistance_class,
                    'description' => 'Dodano automatycznie podczas tworzenia protokołu.'
                ]);
                $this->resistance_class_id = $newClass->id;

                // Odśwież listę
                $this->availableClasses = DoorResistanceClass::orderBy('name')->get();
            }
        }

        $className = '';
        if ($this->resistance_class_id) {
            $classObj = DoorResistanceClass::find($this->resistance_class_id);
            $className = $classObj ? $classObj->name : $this->custom_resistance_class;
        } else {
            $className = $this->custom_resistance_class;
        }

        // Preferujemy ID w inwentarzu
        $inventoryCustomClass = $this->resistance_class_id ? null : $this->custom_resistance_class;
        $inventoryClassId = $this->resistance_class_id ?: null;

        if ($this->editingId) {
            // Edycja
            $door = ProtocolDoor::find($this->editingId);
            $door->update([
                'resistance_class' => $className,
                'location' => $this->location,
            ]);

            if ($door->door_id) {
                $inventory = Door::find($door->door_id);
                if ($inventory) {
                    $inventory->update([
                        'resistance_class_id' => $inventoryClassId,
                        'custom_resistance_class' => $inventoryCustomClass,
                        'location' => $this->location,
                    ]);
                }
            }
        } else {
            // Dodawanie
            $maxOrder = Door::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;

            $inventory = Door::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'resistance_class_id' => $inventoryClassId,
                'custom_resistance_class' => $inventoryCustomClass,
                'location' => $this->location,
                'sort_order' => $maxOrder + 1,
            ]);

            $this->protocol->doors()->create([
                'door_id' => $inventory->id,
                'resistance_class' => $className,
                'location' => $this->location,
                'status' => 'sprawne',
            ]);
        }

        $this->closeModal();
    }

    public function clone($id)
    {
        $original = ProtocolDoor::find($id);
        if ($original) {
            $new = null;

            if ($original->door_id) {
                $inventoryOrig = Door::find($original->door_id);
                if ($inventoryOrig) {
                    $maxOrder = Door::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    $new = $original->replicate();
                    $new->door_id = $inventoryNew->id;
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
        $door = ProtocolDoor::find($id);
        if ($door) {
            $door->delete();
            if ($door->door_id) {
                $inventory = Door::find($door->door_id);
                if ($inventory) {
                    $inventory->update(['is_active' => false]);
                }
            }
        }
    }
}
