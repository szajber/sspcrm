<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\ProtocolVentilationDistributor;
use App\Models\ProtocolVentilationFan;
use App\Models\VentilationDistributor;
use App\Models\VentilationFan;
use Livewire\Component;

class ProtocolVentilationManager extends Component
{
    public Protocol $protocol;

    // Formularz Rozdzielnicy
    public $distName = '';
    public $distLocation = '';

    // Formularz Wentylatora
    public $fanName = '';
    public $fanLocation = '';

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function addDistributor()
    {
        $this->validate([
            'distName' => 'required|string|max:255',
            'distLocation' => 'nullable|string|max:255',
        ]);

        // 1. Dodaj do inwentarza
        $invItem = VentilationDistributor::create([
            'client_object_id' => $this->protocol->clientObject->id,
            'name' => $this->distName,
            'location' => $this->distLocation,
            'is_active' => true,
        ]);

        // 2. Dodaj do protokołu
        $this->protocol->ventilationDistributors()->create([
            'ventilation_distributor_id' => $invItem->id,
            'name' => $this->distName,
            'location' => $this->distLocation,
        ]);

        $this->distName = '';
        $this->distLocation = '';
    }

    public function removeDistributor($id)
    {
        $item = ProtocolVentilationDistributor::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $item->delete();
        }
    }

    public function addFan()
    {
        $this->validate([
            'fanName' => 'required|string|max:255',
            'fanLocation' => 'nullable|string|max:255',
        ]);

        // 1. Dodaj do inwentarza
        $invItem = VentilationFan::create([
            'client_object_id' => $this->protocol->clientObject->id,
            'name' => $this->fanName,
            'location' => $this->fanLocation,
            'is_active' => true,
        ]);

        // 2. Dodaj do protokołu
        $this->protocol->ventilationFans()->create([
            'ventilation_fan_id' => $invItem->id,
            'name' => $this->fanName,
            'location' => $this->fanLocation,
        ]);

        $this->fanName = '';
        $this->fanLocation = '';
    }

    public function removeFan($id)
    {
        $item = ProtocolVentilationFan::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $item->delete();
        }
    }

    public function render()
    {
        return view('livewire.protocol-ventilation-manager', [
            'distributors' => $this->protocol->ventilationDistributors()->orderBy('id')->get(),
            'fans' => $this->protocol->ventilationFans()->orderBy('id')->get(),
        ]);
    }
}
