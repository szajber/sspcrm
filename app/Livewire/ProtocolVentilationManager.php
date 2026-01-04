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

    // Rozdzielnice
    public $showDistributorModal = false;
    public $editingDistributorId = null;
    public $distName = '';
    public $distLocation = '';

    // Wentylatory
    public $showFanModal = false;
    public $editingFanId = null;
    public $fanName = '';
    public $fanLocation = '';

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    // --- Rozdzielnice ---

    public function openDistributorModal()
    {
        $this->editingDistributorId = null;
        $this->distName = '';
        $this->distLocation = '';
        $this->showDistributorModal = true;
    }

    public function editDistributor($id)
    {
        $item = ProtocolVentilationDistributor::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingDistributorId = $id;
            $this->distName = $item->name;
            $this->distLocation = $item->location;
            $this->showDistributorModal = true;
        }
    }

    public function cloneDistributor($id)
    {
        $item = ProtocolVentilationDistributor::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingDistributorId = null; // Nowy element
            $this->distName = $item->name . ' - KOPIA';
            $this->distLocation = $item->location;
            $this->showDistributorModal = true;
        }
    }

    public function saveDistributor()
    {
        $this->validate([
            'distName' => 'required|string|max:255',
            'distLocation' => 'nullable|string|max:255',
        ]);

        if ($this->editingDistributorId) {
            // Edycja
            $item = ProtocolVentilationDistributor::find($this->editingDistributorId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'name' => $this->distName,
                    'location' => $this->distLocation,
                ]);
                // Aktualizacja inwentarza jeśli powiązany
                if ($item->ventilation_distributor_id) {
                    $inv = VentilationDistributor::find($item->ventilation_distributor_id);
                    if ($inv) {
                        $inv->update([
                            'name' => $this->distName,
                            'location' => $this->distLocation,
                        ]);
                    }
                }
            }
        } else {
            // Dodawanie nowego (także klona)
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
        }

        $this->showDistributorModal = false;
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

    // --- Wentylatory ---

    public function openFanModal()
    {
        $this->editingFanId = null;
        $this->fanName = '';
        $this->fanLocation = '';
        $this->showFanModal = true;
    }

    public function editFan($id)
    {
        $item = ProtocolVentilationFan::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingFanId = $id;
            $this->fanName = $item->name;
            $this->fanLocation = $item->location;
            $this->showFanModal = true;
        }
    }

    public function cloneFan($id)
    {
        $item = ProtocolVentilationFan::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingFanId = null;
            $this->fanName = $item->name . ' - KOPIA';
            $this->fanLocation = $item->location;
            $this->showFanModal = true;
        }
    }

    public function saveFan()
    {
        $this->validate([
            'fanName' => 'required|string|max:255',
            'fanLocation' => 'nullable|string|max:255',
        ]);

        if ($this->editingFanId) {
            $item = ProtocolVentilationFan::find($this->editingFanId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'name' => $this->fanName,
                    'location' => $this->fanLocation,
                ]);
                if ($item->ventilation_fan_id) {
                    $inv = VentilationFan::find($item->ventilation_fan_id);
                    if ($inv) {
                        $inv->update([
                            'name' => $this->fanName,
                            'location' => $this->fanLocation,
                        ]);
                    }
                }
            }
        } else {
            $invItem = VentilationFan::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'name' => $this->fanName,
                'location' => $this->fanLocation,
                'is_active' => true,
            ]);

            $maxSort = $this->protocol->ventilationFans()->max('sort_order') ?? 0;

            $this->protocol->ventilationFans()->create([
                'ventilation_fan_id' => $invItem->id,
                'name' => $this->fanName,
                'location' => $this->fanLocation,
                'sort_order' => $maxSort + 1,
            ]);
        }

        $this->showFanModal = false;
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

    public function render()
    {
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
