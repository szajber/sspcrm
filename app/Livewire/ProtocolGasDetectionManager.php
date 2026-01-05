<?php

namespace App\Livewire;

use App\Models\GasDetectionCentral;
use App\Models\GasDetectionControlDevice;
use App\Models\GasDetectionDetector;
use App\Models\Protocol;
use App\Models\ProtocolGasDetectionCentral;
use App\Models\ProtocolGasDetectionControlDevice;
use App\Models\ProtocolGasDetectionDetector;
use Livewire\Component;

class ProtocolGasDetectionManager extends Component
{
    public Protocol $protocol;

    // --- Centrale ---
    public $showCentralModal = false;
    public $editingCentralId = null;
    public $centralName = '';
    public $centralLocation = '';

    // --- Detektory ---
    public $showDetectorModal = false;
    public $editingDetectorId = null;
    public $detectorName = '';
    public $detectorLocation = '';

    // --- Urządzenia Sterujące ---
    public $showControlModal = false;
    public $editingControlId = null;
    public $controlType = 'Wentylacja'; // Default
    public $controlLocation = '';

    public $controlTypes = ['Wentylacja', 'Zawór MAG', 'Sygnalizator', 'Lampa'];

    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    // --- Metody dla Central ---
    public function openCentralModal()
    {
        $this->editingCentralId = null;
        $this->centralName = '';
        $this->centralLocation = '';
        $this->showCentralModal = true;
    }

    public function editCentral($id)
    {
        $item = ProtocolGasDetectionCentral::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingCentralId = $id;
            $this->centralName = $item->name;
            $this->centralLocation = $item->location;
            $this->showCentralModal = true;
        }
    }

    public function cloneCentral($id)
    {
        $item = ProtocolGasDetectionCentral::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingCentralId = null;
            $this->centralName = $item->name . ' - KOPIA';
            $this->centralLocation = $item->location;
            $this->showCentralModal = true;
        }
    }

    public function saveCentral()
    {
        $this->validate([
            'centralName' => 'required|string|max:255',
            'centralLocation' => 'nullable|string|max:255',
        ]);

        if ($this->editingCentralId) {
            $item = ProtocolGasDetectionCentral::find($this->editingCentralId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'name' => $this->centralName,
                    'location' => $this->centralLocation,
                ]);
                if ($item->gas_detection_central_id) {
                    $inv = GasDetectionCentral::find($item->gas_detection_central_id);
                    if ($inv) $inv->update(['name' => $this->centralName, 'location' => $this->centralLocation]);
                }
            }
        } else {
            $invItem = GasDetectionCentral::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'name' => $this->centralName,
                'location' => $this->centralLocation,
                'is_active' => true,
            ]);
            $maxSort = $this->protocol->gasDetectionCentrals()->max('sort_order') ?? 0;
            $this->protocol->gasDetectionCentrals()->create([
                'gas_detection_central_id' => $invItem->id,
                'name' => $this->centralName,
                'location' => $this->centralLocation,
                'sort_order' => $maxSort + 1,
            ]);
        }
        $this->showCentralModal = false;
        $this->centralName = '';
        $this->centralLocation = '';
    }

    public function removeCentral($id)
    {
        $item = ProtocolGasDetectionCentral::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $item->delete();
        }
    }

    // --- Metody dla Detektorów ---
    public function openDetectorModal()
    {
        $this->editingDetectorId = null;
        $this->detectorName = '';
        $this->detectorLocation = '';
        $this->showDetectorModal = true;
    }

    public function editDetector($id)
    {
        $item = ProtocolGasDetectionDetector::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingDetectorId = $id;
            $this->detectorName = $item->name;
            $this->detectorLocation = $item->location;
            $this->showDetectorModal = true;
        }
    }

    public function cloneDetector($id)
    {
        $item = ProtocolGasDetectionDetector::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingDetectorId = null;
            $this->detectorName = $item->name . ' - KOPIA';
            $this->detectorLocation = $item->location;
            $this->showDetectorModal = true;
        }
    }

    public function saveDetector()
    {
        $this->validate([
            'detectorName' => 'required|string|max:255',
            'detectorLocation' => 'nullable|string|max:255',
        ]);

        if ($this->editingDetectorId) {
            $item = ProtocolGasDetectionDetector::find($this->editingDetectorId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'name' => $this->detectorName,
                    'location' => $this->detectorLocation,
                ]);
                if ($item->gas_detection_detector_id) {
                    $inv = GasDetectionDetector::find($item->gas_detection_detector_id);
                    if ($inv) $inv->update(['name' => $this->detectorName, 'location' => $this->detectorLocation]);
                }
            }
        } else {
            $invItem = GasDetectionDetector::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'name' => $this->detectorName,
                'location' => $this->detectorLocation,
                'is_active' => true,
            ]);
            $maxSort = $this->protocol->gasDetectionDetectors()->max('sort_order') ?? 0;
            $this->protocol->gasDetectionDetectors()->create([
                'gas_detection_detector_id' => $invItem->id,
                'name' => $this->detectorName,
                'location' => $this->detectorLocation,
                'sort_order' => $maxSort + 1,
            ]);
        }
        $this->showDetectorModal = false;
        $this->detectorName = '';
        $this->detectorLocation = '';
    }

    public function removeDetector($id)
    {
        $item = ProtocolGasDetectionDetector::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $item->delete();
        }
    }

    // --- Metody dla Urządzeń Sterujących ---
    public function openControlModal()
    {
        $this->editingControlId = null;
        $this->controlType = 'Wentylacja';
        $this->controlLocation = '';
        $this->showControlModal = true;
    }

    public function editControl($id)
    {
        $item = ProtocolGasDetectionControlDevice::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingControlId = $id;
            $this->controlType = $item->type;
            $this->controlLocation = $item->location;
            $this->showControlModal = true;
        }
    }

    public function cloneControl($id)
    {
        $item = ProtocolGasDetectionControlDevice::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingControlId = null;
            $this->controlType = $item->type;
            $this->controlLocation = $item->location . ' - KOPIA';
            $this->showControlModal = true;
        }
    }

    public function saveControl()
    {
        $this->validate([
            'controlType' => 'required|string|in:' . implode(',', $this->controlTypes),
            'controlLocation' => 'nullable|string|max:255',
        ]);

        if ($this->editingControlId) {
            $item = ProtocolGasDetectionControlDevice::find($this->editingControlId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'type' => $this->controlType,
                    'location' => $this->controlLocation,
                ]);
                if ($item->gas_detection_control_device_id) {
                    $inv = GasDetectionControlDevice::find($item->gas_detection_control_device_id);
                    if ($inv) $inv->update(['type' => $this->controlType, 'location' => $this->controlLocation]);
                }
            }
        } else {
            $invItem = GasDetectionControlDevice::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'type' => $this->controlType,
                'location' => $this->controlLocation,
                'is_active' => true,
            ]);
            $maxSort = $this->protocol->gasDetectionControlDevices()->max('sort_order') ?? 0;
            $this->protocol->gasDetectionControlDevices()->create([
                'gas_detection_control_device_id' => $invItem->id,
                'type' => $this->controlType,
                'location' => $this->controlLocation,
                'sort_order' => $maxSort + 1,
            ]);
        }
        $this->showControlModal = false;
        $this->controlType = 'Wentylacja';
        $this->controlLocation = '';
    }

    public function removeControl($id)
    {
        $item = ProtocolGasDetectionControlDevice::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $item->delete();
        }
    }

    // --- Sortowanie ---
    public function moveCentralUp($id) { $this->moveItemUp(ProtocolGasDetectionCentral::class, $id); }
    public function moveCentralDown($id) { $this->moveItemDown(ProtocolGasDetectionCentral::class, $id); }

    public function moveDetectorUp($id) { $this->moveItemUp(ProtocolGasDetectionDetector::class, $id); }
    public function moveDetectorDown($id) { $this->moveItemDown(ProtocolGasDetectionDetector::class, $id); }

    public function moveControlUp($id) { $this->moveItemUp(ProtocolGasDetectionControlDevice::class, $id); }
    public function moveControlDown($id) { $this->moveItemDown(ProtocolGasDetectionControlDevice::class, $id); }

    protected function moveItemUp($modelClass, $id)
    {
        $current = $modelClass::find($id);
        if (!$current || $current->protocol_id !== $this->protocol->id) return;

        $prev = $modelClass::where('protocol_id', $this->protocol->id)
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
            $items = $modelClass::where('protocol_id', $this->protocol->id)->orderBy('sort_order')->orderBy('id')->get();
            foreach($items as $idx => $item) $item->update(['sort_order' => $idx + 1]);
        }
    }

    protected function moveItemDown($modelClass, $id)
    {
        $current = $modelClass::find($id);
        if (!$current || $current->protocol_id !== $this->protocol->id) return;

        $next = $modelClass::where('protocol_id', $this->protocol->id)
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
             $items = $modelClass::where('protocol_id', $this->protocol->id)->orderBy('sort_order')->orderBy('id')->get();
            foreach($items as $idx => $item) $item->update(['sort_order' => $idx + 1]);
        }
    }

    public function render()
    {
        return view('livewire.protocol-gas-detection-manager', [
            'centrals' => $this->protocol->gasDetectionCentrals()->orderBy('sort_order')->orderBy('id')->get(),
            'detectors' => $this->protocol->gasDetectionDetectors()->orderBy('sort_order')->orderBy('id')->get(),
            'controls' => $this->protocol->gasDetectionControlDevices()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}
