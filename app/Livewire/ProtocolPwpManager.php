<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\PwpDevice;
use App\Models\ProtocolPwpDevice;
use Livewire\Component;

class ProtocolPwpManager extends Component
{
    public Protocol $protocol;

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $type;
    public $location;
    public $system_number = 1;

    protected $rules = [
        'type' => 'required|in:switch,trigger',
        'location' => 'nullable|string|max:255',
        'system_number' => 'required|integer|min:1',
    ];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function render()
    {
        // Pobieramy urządzenia posortowane według kolejności z inwentarza
        $devices = ProtocolPwpDevice::where('protocol_id', $this->protocol->id)
            ->leftJoin('pwp_devices', 'protocol_pwp_devices.pwp_device_id', '=', 'pwp_devices.id')
            ->select('protocol_pwp_devices.*')
            ->orderBy('protocol_pwp_devices.system_number')
            ->orderBy('pwp_devices.sort_order')
            ->orderBy('protocol_pwp_devices.id')
            ->get();

        return view('livewire.protocol-pwp-manager', [
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
        $this->type = 'trigger'; // domyślnie
        $this->location = '';
        $this->system_number = 1;
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $device = ProtocolPwpDevice::find($id);
        if ($device) {
            $this->editingId = $id;
            $this->type = $device->type;
            $this->location = $device->location;
            $this->system_number = $device->system_number;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $protocolDevice = ProtocolPwpDevice::find($this->editingId);
            if ($protocolDevice) {
                $protocolDevice->update([
                    'type' => $this->type,
                    'location' => $this->location,
                    'system_number' => $this->system_number,
                ]);

                if ($protocolDevice->pwp_device_id) {
                    $inventory = PwpDevice::find($protocolDevice->pwp_device_id);
                    if ($inventory) {
                        $inventory->update([
                            'type' => $this->type,
                            'location' => $this->location,
                            'system_number' => $this->system_number,
                        ]);
                    }
                }
            }
        } else {
            // Dodawanie nowej pozycji
            $maxOrder = PwpDevice::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;

            $inventory = PwpDevice::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'type' => $this->type,
                'location' => $this->location,
                'system_number' => $this->system_number,
                'sort_order' => $maxOrder + 1,
            ]);

            $this->protocol->pwpDevices()->create([
                'pwp_device_id' => $inventory->id,
                'type' => $this->type,
                'location' => $this->location,
                'system_number' => $this->system_number,
                'result' => 'positive',
            ]);
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $device = ProtocolPwpDevice::find($id);
        if ($device) {
            $device->delete();
            // Nie usuwamy z inwentarza fizycznie
        }
    }

    public function clone($id)
    {
        $original = ProtocolPwpDevice::find($id);
        if ($original) {
            $new = null;

            if ($original->pwp_device_id) {
                $inventoryOrig = PwpDevice::find($original->pwp_device_id);
                if ($inventoryOrig) {
                    $maxOrder = PwpDevice::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    $new = $original->replicate();
                    $new->pwp_device_id = $inventoryNew->id;
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
        $current = ProtocolPwpDevice::find($id);
        if (!$current || !$current->pwp_device_id) return;

        $currentInventory = PwpDevice::find($current->pwp_device_id);
        if (!$currentInventory) return;

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';

        $targetInventories = PwpDevice::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', $operator, $currentInventory->sort_order)
            ->orderBy('sort_order', $order)
            ->take($steps)
            ->get();

        if ($targetInventories->isEmpty()) return;

        $targetInventory = $targetInventories->last();

        $rangeQuery = PwpDevice::where('client_object_id', $currentInventory->client_object_id);

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
                PwpDevice::where('id', $itemId)->update(['sort_order' => $orders[$index]]);
            }
        }
    }
}
