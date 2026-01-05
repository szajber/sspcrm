<?php

namespace App\Livewire;

use App\Models\GasDetectionCentral;
use App\Models\GasDetectionControlDevice;
use App\Models\GasDetectionDetector;
use App\Models\GasDetectionCentralType;
use App\Models\GasDetectionDetectorType;
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
    public $centralTypeId = ''; // ID wybranego typu
    public $centralCustomName = ''; // Nazwa wpisana ręcznie
    public $centralLocation = '';

    // --- Detektory ---
    public $showDetectorModal = false;
    public $editingDetectorId = null;
    public $detectorTypeId = ''; // ID wybranego typu
    public $detectorCustomName = ''; // Nazwa wpisana ręcznie
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
        $this->centralTypeId = '';
        $this->centralCustomName = '';
        $this->centralLocation = '';
        $this->showCentralModal = true;
    }

    public function editCentral($id)
    {
        $item = ProtocolGasDetectionCentral::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingCentralId = $id;

            // Próbujemy dopasować typ po nazwie
            $type = GasDetectionCentralType::where('name', $item->name)->first();
            $this->centralTypeId = $type ? $type->id : '';
            $this->centralCustomName = $type ? '' : $item->name;

            $this->centralLocation = $item->location;
            $this->showCentralModal = true;
        }
    }

    public function cloneCentral($id)
    {
        $item = ProtocolGasDetectionCentral::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingCentralId = null;

            // Przy klonowaniu ustawiamy jako custom, chyba że chcemy dopasować
            $this->centralTypeId = '';
            $this->centralCustomName = $item->name . ' - KOPIA';

            $this->centralLocation = $item->location;
            $this->showCentralModal = true;
        }
    }

    public function saveCentral()
    {
        $this->validate([
            'centralTypeId' => 'nullable|exists:gas_detection_central_types,id',
            'centralCustomName' => 'nullable|string|max:255',
            'centralLocation' => 'nullable|string|max:255',
        ]);

        // Automatyczne dodawanie nowego typu
        if (empty($this->centralTypeId) && !empty($this->centralCustomName)) {
            $existingType = GasDetectionCentralType::where('name', $this->centralCustomName)->first();
            if ($existingType) {
                $this->centralTypeId = $existingType->id;
            } else {
                $newType = GasDetectionCentralType::create(['name' => $this->centralCustomName]);
                $this->centralTypeId = $newType->id;
            }
        }

        $finalName = '';
        if ($this->centralTypeId) {
            $type = GasDetectionCentralType::find($this->centralTypeId);
            $finalName = $type ? $type->name : $this->centralCustomName;
        } else {
            $finalName = $this->centralCustomName;
        }

        // Fallback jeśli nazwa pusta (walidacja wyżej powinna to wyłapać, ale upewnijmy się)
        if (empty($finalName)) {
            $this->addError('centralCustomName', 'Musisz wybrać typ lub wpisać nazwę.');
            return;
        }

        if ($this->editingCentralId) {
            $item = ProtocolGasDetectionCentral::find($this->editingCentralId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'name' => $finalName,
                    'location' => $this->centralLocation,
                ]);
                if ($item->gas_detection_central_id) {
                    $inv = GasDetectionCentral::find($item->gas_detection_central_id);
                    if ($inv) $inv->update(['name' => $finalName, 'location' => $this->centralLocation]);
                }
            }
        } else {
            $invItem = GasDetectionCentral::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'name' => $finalName,
                'location' => $this->centralLocation,
                'is_active' => true,
            ]);
            $maxSort = $this->protocol->gasDetectionCentrals()->max('sort_order') ?? 0;
            $this->protocol->gasDetectionCentrals()->create([
                'gas_detection_central_id' => $invItem->id,
                'name' => $finalName,
                'location' => $this->centralLocation,
                'sort_order' => $maxSort + 1,
            ]);
        }
        $this->showCentralModal = false;
        $this->centralTypeId = '';
        $this->centralCustomName = '';
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
        $this->detectorTypeId = '';
        $this->detectorCustomName = '';
        $this->detectorLocation = '';
        $this->showDetectorModal = true;
    }

    public function editDetector($id)
    {
        $item = ProtocolGasDetectionDetector::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingDetectorId = $id;

            $type = GasDetectionDetectorType::where('name', $item->name)->first();
            $this->detectorTypeId = $type ? $type->id : '';
            $this->detectorCustomName = $type ? '' : $item->name;

            $this->detectorLocation = $item->location;
            $this->showDetectorModal = true;
        }
    }

    public function cloneDetector($id)
    {
        $item = ProtocolGasDetectionDetector::find($id);
        if ($item && $item->protocol_id === $this->protocol->id) {
            $this->editingDetectorId = null;
            $this->detectorTypeId = '';
            $this->detectorCustomName = $item->name . ' - KOPIA';
            $this->detectorLocation = $item->location;
            $this->showDetectorModal = true;
        }
    }

    public function saveDetector()
    {
        $this->validate([
            'detectorTypeId' => 'nullable|exists:gas_detection_detector_types,id',
            'detectorCustomName' => 'nullable|string|max:255',
            'detectorLocation' => 'nullable|string|max:255',
        ]);

        // Automatyczne dodawanie nowego typu
        if (empty($this->detectorTypeId) && !empty($this->detectorCustomName)) {
            $existingType = GasDetectionDetectorType::where('name', $this->detectorCustomName)->first();
            if ($existingType) {
                $this->detectorTypeId = $existingType->id;
            } else {
                $newType = GasDetectionDetectorType::create(['name' => $this->detectorCustomName]);
                $this->detectorTypeId = $newType->id;
            }
        }

        $finalName = '';
        if ($this->detectorTypeId) {
            $type = GasDetectionDetectorType::find($this->detectorTypeId);
            $finalName = $type ? $type->name : $this->detectorCustomName;
        } else {
            $finalName = $this->detectorCustomName;
        }

        if (empty($finalName)) {
            $this->addError('detectorCustomName', 'Musisz wybrać typ lub wpisać nazwę.');
            return;
        }

        if ($this->editingDetectorId) {
            $item = ProtocolGasDetectionDetector::find($this->editingDetectorId);
            if ($item && $item->protocol_id === $this->protocol->id) {
                $item->update([
                    'name' => $finalName,
                    'location' => $this->detectorLocation,
                ]);
                if ($item->gas_detection_detector_id) {
                    $inv = GasDetectionDetector::find($item->gas_detection_detector_id);
                    if ($inv) $inv->update(['name' => $finalName, 'location' => $this->detectorLocation]);
                }
            }
        } else {
            $invItem = GasDetectionDetector::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'name' => $finalName,
                'location' => $this->detectorLocation,
                'is_active' => true,
            ]);
            $maxSort = $this->protocol->gasDetectionDetectors()->max('sort_order') ?? 0;
            $this->protocol->gasDetectionDetectors()->create([
                'gas_detection_detector_id' => $invItem->id,
                'name' => $finalName,
                'location' => $this->detectorLocation,
                'sort_order' => $maxSort + 1,
            ]);
        }
        $this->showDetectorModal = false;
        $this->detectorTypeId = '';
        $this->detectorCustomName = '';
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
            'centralTypes' => GasDetectionCentralType::orderBy('name')->get(),
            'detectorTypes' => GasDetectionDetectorType::orderBy('name')->get(),
        ]);
    }
}
