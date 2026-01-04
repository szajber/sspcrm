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

        $maxSort = $this->protocol->ventilationDistributors()->max('sort_order') ?? 0;

        // 2. Dodaj do protokołu
        $this->protocol->ventilationDistributors()->create([
            'ventilation_distributor_id' => $invItem->id,
            'name' => $this->distName,
            'location' => $this->distLocation,
            'sort_order' => $maxSort + 1,
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

    public function moveDistributorUp($id)
    {
        $current = ProtocolVentilationDistributor::find($id);
        if (!$current || $current->protocol_id !== $this->protocol->id) return;

        $prev = ProtocolVentilationDistributor::where('protocol_id', $this->protocol->id)
            ->where('sort_order', '<', $current->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) {
            $temp = $current->sort_order;
            $current->sort_order = $prev->sort_order;
            $prev->sort_order = $temp;
            $current->save();
            $prev->save();
        } else {
             // Jeśli sort_order są równe (np. 0), musimy przeliczyć wszystkie
             $this->reorderDistributors();
        }
    }

    public function moveDistributorDown($id)
    {
        $current = ProtocolVentilationDistributor::find($id);
        if (!$current || $current->protocol_id !== $this->protocol->id) return;

        $next = ProtocolVentilationDistributor::where('protocol_id', $this->protocol->id)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $temp = $current->sort_order;
            $current->sort_order = $next->sort_order;
            $next->sort_order = $temp;
            $current->save();
            $next->save();
        } else {
             $this->reorderDistributors();
        }
    }

    private function reorderDistributors()
    {
        $items = $this->protocol->ventilationDistributors()->orderBy('sort_order')->orderBy('id')->get();
        foreach ($items as $index => $item) {
            $item->update(['sort_order' => $index + 1]);
        }
    }

    public function duplicateDistributor($id)
    {
        $item = ProtocolVentilationDistributor::find($id);
        if (!$item || $item->protocol_id !== $this->protocol->id) return;

        // Klonowanie inwentarza
        $newInvId = null;
        if ($item->ventilation_distributor_id) {
            $originalInv = VentilationDistributor::find($item->ventilation_distributor_id);
            if ($originalInv) {
                $newInv = $originalInv->replicate();
                $newInv->save();
                $newInvId = $newInv->id;
            }
        }

        $maxSort = $this->protocol->ventilationDistributors()->max('sort_order') ?? 0;

        $newItem = $item->replicate();
        $newItem->ventilation_distributor_id = $newInvId;
        $newItem->sort_order = $maxSort + 1;
        $newItem->save();
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

        $maxSort = $this->protocol->ventilationFans()->max('sort_order') ?? 0;

        // 2. Dodaj do protokołu
        $this->protocol->ventilationFans()->create([
            'ventilation_fan_id' => $invItem->id,
            'name' => $this->fanName,
            'location' => $this->fanLocation,
            'sort_order' => $maxSort + 1,
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

    public function moveFanUp($id)
    {
        $current = ProtocolVentilationFan::find($id);
        if (!$current || $current->protocol_id !== $this->protocol->id) return;

        $prev = ProtocolVentilationFan::where('protocol_id', $this->protocol->id)
            ->where('sort_order', '<', $current->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) {
            $temp = $current->sort_order;
            $current->sort_order = $prev->sort_order;
            $prev->sort_order = $temp;
            $current->save();
            $prev->save();
        } else {
             $this->reorderFans();
        }
    }

    public function moveFanDown($id)
    {
        $current = ProtocolVentilationFan::find($id);
        if (!$current || $current->protocol_id !== $this->protocol->id) return;

        $next = ProtocolVentilationFan::where('protocol_id', $this->protocol->id)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $temp = $current->sort_order;
            $current->sort_order = $next->sort_order;
            $next->sort_order = $temp;
            $current->save();
            $next->save();
        } else {
             $this->reorderFans();
        }
    }

    private function reorderFans()
    {
        $items = $this->protocol->ventilationFans()->orderBy('sort_order')->orderBy('id')->get();
        foreach ($items as $index => $item) {
            $item->update(['sort_order' => $index + 1]);
        }
    }

    public function duplicateFan($id)
    {
        $item = ProtocolVentilationFan::find($id);
        if (!$item || $item->protocol_id !== $this->protocol->id) return;

        $newInvId = null;
        if ($item->ventilation_fan_id) {
            $originalInv = VentilationFan::find($item->ventilation_fan_id);
            if ($originalInv) {
                $newInv = $originalInv->replicate();
                $newInv->save();
                $newInvId = $newInv->id;
            }
        }

        $maxSort = $this->protocol->ventilationFans()->max('sort_order') ?? 0;

        $newItem = $item->replicate();
        $newItem->ventilation_fan_id = $newInvId;
        $newItem->sort_order = $maxSort + 1;
        $newItem->save();
    }

    public function render()
    {
        // Upewnij się, że sortowanie jest inicjalizowane jeśli wszystkie mają 0
        if ($this->protocol->ventilationDistributors()->count() > 0 && $this->protocol->ventilationDistributors()->max('sort_order') == 0) {
            $this->reorderDistributors();
        }
        if ($this->protocol->ventilationFans()->count() > 0 && $this->protocol->ventilationFans()->max('sort_order') == 0) {
            $this->reorderFans();
        }

        return view('livewire.protocol-ventilation-manager', [
            'distributors' => $this->protocol->ventilationDistributors()->orderBy('sort_order')->orderBy('id')->get(),
            'fans' => $this->protocol->ventilationFans()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}
