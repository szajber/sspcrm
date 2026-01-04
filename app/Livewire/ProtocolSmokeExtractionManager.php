<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\ProtocolSmokeExtractionSystem;
use App\Models\SmokeExtractionSystem;
use App\Models\SmokeExtractionCentralType;
use Livewire\Component;

class ProtocolSmokeExtractionManager extends Component
{
    public Protocol $protocol;

    // Form properties
    public $editingId = null;
    public $showModal = false;

    public $central_type_id;
    public $custom_central_type;
    public $location;
    public $detectors_count;
    public $buttons_count;
    public $vent_buttons_count;
    public $air_inlet_count;
    public $smoke_exhaust_count;

    public $availableTypes = [];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function render()
    {
        // Pobieramy dostępne typy
        $this->availableTypes = SmokeExtractionCentralType::orderBy('name')->get();

        // Pobieramy systemy posortowane według kolejności z inwentarza
        $systems = ProtocolSmokeExtractionSystem::where('protocol_id', $this->protocol->id)
            ->leftJoin('smoke_extraction_systems', 'protocol_smoke_extraction_systems.smoke_extraction_system_id', '=', 'smoke_extraction_systems.id')
            ->select('protocol_smoke_extraction_systems.*')
            ->orderBy('smoke_extraction_systems.sort_order')
            ->orderBy('protocol_smoke_extraction_systems.id')
            ->get();

        return view('livewire.protocol-smoke-extraction-manager', [
            'systems' => $systems
        ]);
    }

    public function moveUp($id)
    {
        $current = ProtocolSmokeExtractionSystem::find($id);
        if (!$current || !$current->smoke_extraction_system_id) return;

        $currentInventory = SmokeExtractionSystem::find($current->smoke_extraction_system_id);
        if (!$currentInventory) return;

        $previousInventory = SmokeExtractionSystem::where('client_object_id', $currentInventory->client_object_id)
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
        $current = ProtocolSmokeExtractionSystem::find($id);
        if (!$current || !$current->smoke_extraction_system_id) return;

        $currentInventory = SmokeExtractionSystem::find($current->smoke_extraction_system_id);
        if (!$currentInventory) return;

        $nextInventory = SmokeExtractionSystem::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextInventory) {
            $tempOrder = $currentInventory->sort_order;
            $currentInventory->update(['sort_order' => $nextInventory->sort_order]);
            $nextInventory->update(['sort_order' => $tempOrder]);
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
        $this->central_type_id = null;
        $this->custom_central_type = '';
        $this->location = '';
        $this->detectors_count = '';
        $this->buttons_count = 0;
        $this->vent_buttons_count = 0;
        $this->air_inlet_count = 0;
        $this->smoke_exhaust_count = 0;
    }

    public function edit($id)
    {
        $system = ProtocolSmokeExtractionSystem::find($id);
        if ($system) {
            $this->editingId = $id;
            $this->location = $system->location;
            $this->detectors_count = $system->detectors_count;
            $this->buttons_count = $system->buttons_count;
            $this->vent_buttons_count = $system->vent_buttons_count;
            $this->air_inlet_count = $system->air_inlet_count;
            $this->smoke_exhaust_count = $system->smoke_exhaust_count;

            // Próba ustalenia ID typu
            $this->central_type_id = null;
            $this->custom_central_type = '';

            if ($system->smoke_extraction_system_id) {
                $inventory = SmokeExtractionSystem::find($system->smoke_extraction_system_id);
                if ($inventory && $inventory->central_type_id) {
                    $this->central_type_id = $inventory->central_type_id;
                }
            }

            if (!$this->central_type_id && $system->central_type_name) {
                $existingType = SmokeExtractionCentralType::where('name', $system->central_type_name)->first();
                if ($existingType) {
                    $this->central_type_id = $existingType->id;
                } else {
                    $this->custom_central_type = $system->central_type_name;
                }
            }

            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'central_type_id' => 'nullable|exists:smoke_extraction_central_types,id',
            'custom_central_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'detectors_count' => 'nullable|string|max:255',
            'buttons_count' => 'required|integer|min:0',
            'vent_buttons_count' => 'required|integer|min:0',
            'air_inlet_count' => 'required|integer|min:0',
            'smoke_exhaust_count' => 'required|integer|min:0',
        ]);

        // Automatyczne dodawanie nowego typu do słownika
        if (empty($this->central_type_id) && !empty($this->custom_central_type)) {
            $existingType = SmokeExtractionCentralType::where('name', $this->custom_central_type)->first();

            if ($existingType) {
                $this->central_type_id = $existingType->id;
            } else {
                $newType = SmokeExtractionCentralType::create([
                    'name' => $this->custom_central_type,
                    'description' => 'Dodano automatycznie podczas tworzenia protokołu.'
                ]);
                $this->central_type_id = $newType->id;
                $this->availableTypes = SmokeExtractionCentralType::orderBy('name')->get();
            }
        }

        $typeName = '';
        if ($this->central_type_id) {
            $typeObj = SmokeExtractionCentralType::find($this->central_type_id);
            $typeName = $typeObj ? $typeObj->name : $this->custom_central_type;
        } else {
            $typeName = $this->custom_central_type;
        }

        // Preferujemy ID w inwentarzu
        $inventoryCustomType = $this->central_type_id ? null : $this->custom_central_type;
        $inventoryTypeId = $this->central_type_id ?: null;

        if ($this->editingId) {
            // Edycja
            $system = ProtocolSmokeExtractionSystem::find($this->editingId);
            $system->update([
                'central_type_name' => $typeName,
                'location' => $this->location,
                'detectors_count' => $this->detectors_count,
                'buttons_count' => $this->buttons_count,
                'vent_buttons_count' => $this->vent_buttons_count,
                'air_inlet_count' => $this->air_inlet_count,
                'smoke_exhaust_count' => $this->smoke_exhaust_count,
            ]);

            if ($system->smoke_extraction_system_id) {
                $inventory = SmokeExtractionSystem::find($system->smoke_extraction_system_id);
                if ($inventory) {
                    $inventory->update([
                        'central_type_id' => $inventoryTypeId,
                        'custom_central_type' => $inventoryCustomType,
                        'location' => $this->location,
                        'detectors_count' => $this->detectors_count,
                        'buttons_count' => $this->buttons_count,
                        'vent_buttons_count' => $this->vent_buttons_count,
                        'air_inlet_count' => $this->air_inlet_count,
                        'smoke_exhaust_count' => $this->smoke_exhaust_count,
                    ]);
                }
            }
        } else {
            // Dodawanie
            $maxOrder = SmokeExtractionSystem::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;

            $inventory = SmokeExtractionSystem::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'central_type_id' => $inventoryTypeId,
                'custom_central_type' => $inventoryCustomType,
                'location' => $this->location,
                'detectors_count' => $this->detectors_count,
                'buttons_count' => $this->buttons_count,
                'vent_buttons_count' => $this->vent_buttons_count,
                'air_inlet_count' => $this->air_inlet_count,
                'smoke_exhaust_count' => $this->smoke_exhaust_count,
                'sort_order' => $maxOrder + 1,
            ]);

            $this->protocol->smokeExtractionSystems()->create([
                'smoke_extraction_system_id' => $inventory->id,
                'central_type_name' => $typeName,
                'location' => $this->location,
                'detectors_count' => $this->detectors_count,
                'buttons_count' => $this->buttons_count,
                'vent_buttons_count' => $this->vent_buttons_count,
                'air_inlet_count' => $this->air_inlet_count,
                'smoke_exhaust_count' => $this->smoke_exhaust_count,
                'result' => 'positive',
            ]);
        }

        $this->closeModal();
    }

    public function clone($id)
    {
        $original = ProtocolSmokeExtractionSystem::find($id);
        if ($original) {
            $new = null;

            if ($original->smoke_extraction_system_id) {
                $inventoryOrig = SmokeExtractionSystem::find($original->smoke_extraction_system_id);
                if ($inventoryOrig) {
                    $maxOrder = SmokeExtractionSystem::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    $new = $original->replicate();
                    $new->smoke_extraction_system_id = $inventoryNew->id;
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
        $system = ProtocolSmokeExtractionSystem::find($id);
        if ($system) {
            $system->delete();
            if ($system->smoke_extraction_system_id) {
                $inventory = SmokeExtractionSystem::find($system->smoke_extraction_system_id);
                if ($inventory) {
                    $inventory->update(['is_active' => false]);
                }
            }
        }
    }
}
