<?php

namespace App\Livewire;

use App\Models\EmergencyLightingDevice;
use App\Models\Protocol;
use Livewire\Component;

class ProtocolEmergencyLightingManager extends Component
{
    public Protocol $protocol;
    public $devices = [];
    public $type;
    public $location;
    public $editId = null;

    protected $rules = [
        'type' => 'required|string',
        'location' => 'nullable|string|max:255',
    ];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
        $this->loadDevices();
    }

    public function loadDevices()
    {
        $this->devices = EmergencyLightingDevice::where('client_object_id', $this->protocol->client_object_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $device = EmergencyLightingDevice::find($this->editId);
            if ($device) {
                $device->update([
                    'type' => $this->type,
                    'location' => $this->location,
                ]);
            }
            $this->editId = null;
        } else {
            EmergencyLightingDevice::create([
                'client_object_id' => $this->protocol->client_object_id,
                'type' => $this->type,
                'location' => $this->location,
            ]);
        }

        $this->reset(['type', 'location']);
        $this->loadDevices();
    }

    public function edit($id)
    {
        $device = EmergencyLightingDevice::find($id);
        if ($device) {
            $this->editId = $device->id;
            $this->type = $device->type;
            $this->location = $device->location;
        }
    }

    public function delete($id)
    {
        EmergencyLightingDevice::destroy($id);
        $this->loadDevices();
    }

    public function cancelEdit()
    {
        $this->editId = null;
        $this->reset(['type', 'location']);
    }

    public function render()
    {
        return view('livewire.protocol-emergency-lighting-manager');
    }
}
