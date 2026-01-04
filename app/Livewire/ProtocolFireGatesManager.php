<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\FireGateDevice;
use App\Models\ProtocolFireGateDevice;
use Livewire\Component;

class ProtocolFireGatesManager extends Component
{
    public Protocol $protocol;

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $type;
    public $system_number = 1;
    public $location;
    public $gate_type = 'gravitational'; // default
    public $fire_resistance_class;
    public $manufacturer;
    public $model;

    protected $rules = [
        'type' => 'required|in:gate,central',
        'system_number' => 'required|integer|min:1',
        'location' => 'nullable|string|max:255',
        'gate_type' => 'nullable|required_if:type,gate|in:gravitational,electric',
        'fire_resistance_class' => 'nullable|string|max:255',
        'manufacturer' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
    ];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function render()
    {
        $devices = ProtocolFireGateDevice::where('protocol_id', $this->protocol->id)
            ->leftJoin('fire_gate_devices', 'protocol_fire_gate_devices.fire_gate_device_id', '=', 'fire_gate_devices.id')
            ->select('protocol_fire_gate_devices.*')
            ->orderBy('protocol_fire_gate_devices.system_number')
            ->orderBy('fire_gate_devices.sort_order')
            ->orderBy('protocol_fire_gate_devices.id')
            ->get();

        return view('livewire.protocol-fire-gates-manager', [
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
        $this->type = 'gate';
        $this->system_number = 1;
        $this->location = '';
        $this->gate_type = 'gravitational';
        $this->fire_resistance_class = '';
        $this->manufacturer = '';
        $this->model = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $device = ProtocolFireGateDevice::find($id);
        if ($device) {
            $this->editingId = $id;
            $this->type = $device->type;
            $this->system_number = $device->system_number;
            $this->location = $device->location;
            $this->gate_type = $device->gate_type ?? 'gravitational';
            $this->fire_resistance_class = $device->fire_resistance_class;
            $this->manufacturer = $device->manufacturer;
            $this->model = $device->model;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $protocolDevice = ProtocolFireGateDevice::find($this->editingId);
            if ($protocolDevice) {
                $protocolDevice->update([
                    'type' => $this->type,
                    'system_number' => $this->system_number,
                    'location' => $this->location,
                    'gate_type' => $this->type === 'gate' ? $this->gate_type : null,
                    'fire_resistance_class' => $this->type === 'gate' ? $this->fire_resistance_class : null,
                    'manufacturer' => $this->manufacturer,
                    'model' => $this->model,
                ]);

                if ($protocolDevice->fire_gate_device_id) {
                    $inventory = FireGateDevice::find($protocolDevice->fire_gate_device_id);
                    if ($inventory) {
                        $inventory->update([
                            'type' => $this->type,
                            'system_number' => $this->system_number,
                            'location' => $this->location,
                            'gate_type' => $this->type === 'gate' ? $this->gate_type : null,
                            'fire_resistance_class' => $this->type === 'gate' ? $this->fire_resistance_class : null,
                            'manufacturer' => $this->manufacturer,
                            'model' => $this->model,
                        ]);
                    }
                }
            }
        } else {
            $maxOrder = FireGateDevice::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;

            $inventory = FireGateDevice::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'type' => $this->type,
                'system_number' => $this->system_number,
                'location' => $this->location,
                'gate_type' => $this->type === 'gate' ? $this->gate_type : null,
                'fire_resistance_class' => $this->type === 'gate' ? $this->fire_resistance_class : null,
                'manufacturer' => $this->manufacturer,
                'model' => $this->model,
                'sort_order' => $maxOrder + 1,
            ]);

            $this->protocol->fireGateDevices()->create([
                'fire_gate_device_id' => $inventory->id,
                'type' => $this->type,
                'system_number' => $this->system_number,
                'location' => $this->location,
                'gate_type' => $this->type === 'gate' ? $this->gate_type : null,
                'fire_resistance_class' => $this->type === 'gate' ? $this->fire_resistance_class : null,
                'manufacturer' => $this->manufacturer,
                'model' => $this->model,
                'result' => 'positive',
            ]);
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $device = ProtocolFireGateDevice::find($id);
        if ($device) {
            $device->delete();
        }
    }

    public function clone($id)
    {
        $original = ProtocolFireGateDevice::find($id);
        if ($original) {
            $new = null;

            if ($original->fire_gate_device_id) {
                $inventoryOrig = FireGateDevice::find($original->fire_gate_device_id);
                if ($inventoryOrig) {
                    $maxOrder = FireGateDevice::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    $new = $original->replicate();
                    $new->fire_gate_device_id = $inventoryNew->id;
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

    public function moveUp($id) { $this->moveItem($id, 'up', 1); }
    public function moveDown($id) { $this->moveItem($id, 'down', 1); }
    public function moveUp10($id) { $this->moveItem($id, 'up', 10); }
    public function moveDown10($id) { $this->moveItem($id, 'down', 10); }

    protected function moveItem($id, $direction, $steps)
    {
        $current = ProtocolFireGateDevice::find($id);
        if (!$current || !$current->fire_gate_device_id) return;

        $currentInventory = FireGateDevice::find($current->fire_gate_device_id);
        if (!$currentInventory) return;

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';

        $targetInventories = FireGateDevice::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', $operator, $currentInventory->sort_order)
            ->orderBy('sort_order', $order)
            ->take($steps)
            ->get();

        if ($targetInventories->isEmpty()) return;

        $targetInventory = $targetInventories->last();

        $rangeQuery = FireGateDevice::where('client_object_id', $currentInventory->client_object_id);

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
                $currentId = array_pop($ids);
                array_unshift($ids, $currentId);
            } else {
                $currentId = array_shift($ids);
                array_push($ids, $currentId);
            }

            foreach ($ids as $index => $itemId) {
                FireGateDevice::where('id', $itemId)->update(['sort_order' => $orders[$index]]);
            }
        }
    }
}
