<?php

namespace App\Livewire;

use App\Models\EmergencyLightingDevice;
use App\Models\Protocol;
use App\Models\ProtocolEmergencyLightingDevice;
use Livewire\Component;

class ProtocolEmergencyLightingManager extends Component
{
    public Protocol $protocol;

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $type;
    public $location;

    protected $rules = [
        'type' => 'required|string',
        'location' => 'nullable|string|max:255',
    ];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function render()
    {
        // Pobieramy urządzenia posortowane według kolejności z inwentarza
        // Łączymy z tabelą inventory żeby mieć dostęp do sort_order
        $devices = ProtocolEmergencyLightingDevice::where('protocol_id', $this->protocol->id)
            ->leftJoin('emergency_lighting_devices', 'protocol_emergency_lighting_devices.emergency_lighting_device_id', '=', 'emergency_lighting_devices.id')
            ->select('protocol_emergency_lighting_devices.*')
            ->orderBy('emergency_lighting_devices.sort_order')
            ->orderBy('protocol_emergency_lighting_devices.id')
            ->get();

        return view('livewire.protocol-emergency-lighting-manager', [
            'devices' => $devices
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
        $this->type = '';
        $this->location = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $device = ProtocolEmergencyLightingDevice::find($id);
        if ($device) {
            $this->editingId = $id;
            $this->type = $device->type;
            $this->location = $device->location;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            // Edycja istniejącej pozycji w protokole
            $protocolDevice = ProtocolEmergencyLightingDevice::find($this->editingId);
            if ($protocolDevice) {
                $protocolDevice->update([
                    'type' => $this->type,
                    'location' => $this->location,
                ]);

                // Aktualizacja w inwentarzu (jeśli powiązana)
                if ($protocolDevice->emergency_lighting_device_id) {
                    $inventory = EmergencyLightingDevice::find($protocolDevice->emergency_lighting_device_id);
                    if ($inventory) {
                        $inventory->update([
                            'type' => $this->type,
                            'location' => $this->location,
                        ]);
                    }
                }
            }
        } else {
            // Dodawanie nowej pozycji
            // 1. Dodajemy do inwentarza
            $maxOrder = EmergencyLightingDevice::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;

            $inventory = EmergencyLightingDevice::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'type' => $this->type,
                'location' => $this->location,
                'sort_order' => $maxOrder + 1,
            ]);

            // 2. Dodajemy do protokołu
            $this->protocol->emergencyLightingDevices()->create([
                'emergency_lighting_device_id' => $inventory->id,
                'type' => $this->type,
                'location' => $this->location,
                'result' => 'positive', // Domyślny wynik
            ]);
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $device = ProtocolEmergencyLightingDevice::find($id);
        if ($device) {
            $device->delete();

            // Opcjonalnie: Ukrywamy w inwentarzu (soft delete logiczne) - na razie nie usuwamy z inwentarza fizycznie
            // ale w przyszłości można dodać flagę is_active
        }
    }

    public function clone($id)
    {
        $original = ProtocolEmergencyLightingDevice::find($id);
        if ($original) {
            $new = null;

            // Klonujemy w inwentarzu
            if ($original->emergency_lighting_device_id) {
                $inventoryOrig = EmergencyLightingDevice::find($original->emergency_lighting_device_id);
                if ($inventoryOrig) {
                    $maxOrder = EmergencyLightingDevice::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    // Dodajemy do protokołu
                    $new = $original->replicate();
                    $new->emergency_lighting_device_id = $inventoryNew->id;
                    $new->save();
                }
            } else {
                 // Jeśli brak powiązania, tylko w protokole
                 $new = $original->replicate();
                 $new->save();
            }

            // Otwórz edycję nowego urządzenia
            if ($new) {
                $this->edit($new->id);
            }
        }
    }

    public function moveUp($id)
    {
        $this->moveItem($id, 'up', 1);
    }

    public function moveDown($id)
    {
        $this->moveItem($id, 'down', 1);
    }

    public function moveUp10($id)
    {
        $this->moveItem($id, 'up', 10);
    }

    public function moveDown10($id)
    {
        $this->moveItem($id, 'down', 10);
    }

    protected function moveItem($id, $direction, $steps)
    {
        $current = ProtocolEmergencyLightingDevice::find($id);
        if (!$current || !$current->emergency_lighting_device_id) return;

        $currentInventory = EmergencyLightingDevice::find($current->emergency_lighting_device_id);
        if (!$currentInventory) return;

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';

        // Pobierz elementy do przesunięcia
        $targetInventories = EmergencyLightingDevice::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', $operator, $currentInventory->sort_order)
            ->orderBy('sort_order', $order)
            ->take($steps)
            ->get();

        if ($targetInventories->isEmpty()) return;

        $targetInventory = $targetInventories->last();

        // Pobierz zakres do przeorganizowania
        $rangeQuery = EmergencyLightingDevice::where('client_object_id', $currentInventory->client_object_id);

        if ($direction === 'up') {
            $rangeQuery->where('sort_order', '>=', $targetInventory->sort_order)
                       ->where('sort_order', '<=', $currentInventory->sort_order);
        } else {
            $rangeQuery->where('sort_order', '>=', $currentInventory->sort_order)
                       ->where('sort_order', '<=', $targetInventory->sort_order);
        }

        $range = $rangeQuery->orderBy('sort_order', 'asc')->get();

        if ($range->count() > 1) {
            $ids = $range->pluck('id')->toArray();
            $orders = $range->pluck('sort_order')->toArray();

            if ($direction === 'up') {
                // Ostatni (current) idzie na początek
                $currentId = array_pop($ids);
                array_unshift($ids, $currentId);
            } else {
                // Pierwszy (current) idzie na koniec
                $currentId = array_shift($ids);
                array_push($ids, $currentId);
            }

            foreach ($ids as $index => $itemId) {
                EmergencyLightingDevice::where('id', $itemId)->update(['sort_order' => $orders[$index]]);
            }
        }
    }
}
