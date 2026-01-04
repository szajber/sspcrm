<?php

namespace App\Http\Controllers;

use App\Models\ClientObject;
use App\Models\CompanySetting;
use App\Models\Protocol;
use App\Models\ProtocolTemplate;
use App\Models\System;
use App\Models\FireExtinguisher;
use App\Models\ProtocolFireExtinguisher;
use App\Models\Door;
use App\Models\ProtocolDoor;
use App\Models\FireDamper;
use App\Models\ProtocolFireDamper;
use App\Models\SmokeExtractionSystem;
use App\Models\ProtocolSmokeExtractionSystem;
use App\Models\EmergencyLightingDevice;
use App\Models\ProtocolEmergencyLightingDevice;
use App\Models\PwpDevice;
use App\Models\ProtocolPwpDevice;
use App\Models\FireGateDevice;
use App\Models\ProtocolFireGateDevice;
use App\Models\VentilationDistributor;
use App\Models\ProtocolVentilationDistributor;
use App\Models\VentilationFan;
use App\Models\ProtocolVentilationFan;
use Illuminate\Support\Str;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProtocolController extends Controller
{
    public function index(ClientObject $object, System $system)
    {
        $protocols = Protocol::where('client_object_id', $object->id)
            ->where('system_id', $system->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('protocols.index', compact('object', 'system', 'protocols'));
    }

    /**
     * Krok 1: Wybór szablonu, dat i wykonawcy
     */
    public function create(ClientObject $object, System $system)
    {
        $templates = $system->protocolTemplates()->orderBy('is_default', 'desc')->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('protocols.create', compact('object', 'system', 'templates', 'users'));
    }

    /**
     * Zapisuje Krok 1 i przechodzi do Kroku 2 (Dane systemu)
     */
    public function storeStep1(Request $request, ClientObject $object, System $system)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:protocol_templates,id',
            'date' => 'required|date',
            'next_date_option' => $system->has_periodic_review ? 'required|in:3,6,12,none' : 'nullable',
            'performer_id' => 'required|exists:users,id',
        ]);

        // Oblicz datę następnego przeglądu
        $nextDate = null;
        if ($system->has_periodic_review && $validated['next_date_option'] !== 'none') {
            $nextDate = Carbon::parse($validated['date'])->addMonths((int)$validated['next_date_option']);
        }

        // Przygotuj dane do sesji (tymczasowo) lub zapisz draft protokołu
        // Tutaj zapiszemy draft protokołu w bazie

        // Generowanie numeru
        // Format: PREFIX/NUMER/ROK
        $year = Carbon::parse($validated['date'])->year;
        $maxIndex = Protocol::whereYear('date', $year)->max('number_index') ?? 0;
        $newIndex = $maxIndex + 1;
        $number = sprintf('%s/%d/%d', $system->prefix, $newIndex, $year);

        $protocol = Protocol::create([
            'client_object_id' => $object->id,
            'system_id' => $system->id,
            'performer_id' => $validated['performer_id'],
            'number' => $number,
            'number_index' => $newIndex,
            'date' => $validated['date'],
            'next_date' => $nextDate,
            'status' => 'draft',
            'data' => [
                'template_id' => $validated['template_id'],
                // Miejsce na inne dane z kroku 1
            ]
        ]);

        return redirect()->route('protocols.step2', $protocol);
    }

    /**
     * Krok 2: Zarządzanie listą (inwentaryzacja)
     */
    public function step2(Protocol $protocol)
    {
        // Sprawdź czy to jest pierwszy raz kiedy wchodzimy do kroku 2 (brak elementów w protokole)
        // Jeśli tak, spróbuj skopiować z poprzedniego protokołu LUB z inwentarza
        // ALE tylko jeśli nie ma jeszcze nic przypisanego do tego protokołu

        if ($protocol->system->slug === 'gasnice') {
            $protocolExtinguishers = $protocol->fireExtinguishers()->orderBy('id')->get();

            if ($protocolExtinguishers->isEmpty()) {
                // 1. Próba pobrania z poprzedniego protokołu
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id) // Tylko starsze
                    ->where('status', 'completed') // Tylko zakończone (opcjonalnie, ale bezpieczniej)
                    ->orderBy('date', 'desc')
                    ->first();

                $copied = false;
                if ($lastProtocol && $lastProtocol->fireExtinguishers()->exists()) {
                    foreach ($lastProtocol->fireExtinguishers as $prevExtinguisher) {
                        $inventoryItem = FireExtinguisher::find($prevExtinguisher->fire_extinguisher_id);
                        // Kopiujemy tylko jeśli element inwentarza nadal istnieje i jest aktywny
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->fireExtinguishers()->create([
                                'fire_extinguisher_id' => $prevExtinguisher->fire_extinguisher_id,
                                'type_name' => $prevExtinguisher->type_name,
                                'location' => $prevExtinguisher->location,
                                'status' => $prevExtinguisher->status,
                                'next_service_year' => $prevExtinguisher->next_service_year,
                                'notes' => $prevExtinguisher->notes,
                            ]);
                            $copied = true;
                        }
                    }
                }

                // 2. Jeśli nie udało się skopiować (brak poprzedniego protokołu lub pusty), pobierz z inwentarza
                if (!$copied) {
                    $inventory = FireExtinguisher::where('client_object_id', $protocol->clientObject->id)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->fireExtinguishers()->create([
                            'fire_extinguisher_id' => $item->id,
                            'type_name' => $item->type_name,
                            'location' => $item->location,
                            'next_service_year' => $item->next_service_year,
                            'status' => 'legalizacja',
                        ]);
                    }
                }
                $protocolExtinguishers = $protocol->fireExtinguishers()->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolExtinguishers'));
        }

        if ($protocol->system->slug === 'drzwi-przeciwpozarowe') {
            $protocolDoors = $protocol->doors()->orderBy('id')->get();

            if ($protocolDoors->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->doors()->exists()) {
                    foreach ($lastProtocol->doors as $prevDoor) {
                        $inventoryItem = Door::find($prevDoor->door_id);
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->doors()->create([
                                'door_id' => $prevDoor->door_id,
                                'resistance_class' => $prevDoor->resistance_class,
                                'location' => $prevDoor->location,
                                'status' => $prevDoor->status,
                                'notes' => $prevDoor->notes,
                            ]);
                        }
                    }
                } else {
                    $inventory = Door::where('client_object_id', $protocol->clientObject->id)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->doors()->create([
                            'door_id' => $item->id,
                            'resistance_class' => $item->resistance_class,
                            'location' => $item->location,
                            'status' => 'sprawne',
                        ]);
                    }
                }
                $protocolDoors = $protocol->doors()->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolDoors'));
        }

        if ($protocol->system->slug === 'klapy-pozarowe') {
            $protocolDampers = $protocol->fireDampers()->orderBy('id')->get();

            if ($protocolDampers->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->fireDampers()->exists()) {
                    foreach ($lastProtocol->fireDampers as $prevDamper) {
                        $inventoryItem = FireDamper::find($prevDamper->fire_damper_id);
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->fireDampers()->create([
                                'fire_damper_id' => $prevDamper->fire_damper_id,
                                'type_name' => $prevDamper->type_name,
                                'location' => $prevDamper->location,
                                'manufacturer' => $prevDamper->manufacturer,
                                'result' => $prevDamper->result,
                                'notes' => $prevDamper->notes,
                            ]);
                        }
                    }
                } else {
                    $inventory = FireDamper::where('client_object_id', $protocol->clientObject->id)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->fireDampers()->create([
                            'fire_damper_id' => $item->id,
                            'type_name' => $item->type_name,
                            'location' => $item->location,
                            'manufacturer' => $item->manufacturer,
                            'result' => 'positive',
                        ]);
                    }
                }
                $protocolDampers = $protocol->fireDampers()->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolDampers'));
        }

        if ($protocol->system->slug === 'system-oddymiania') {
            $protocolSmokeSystems = $protocol->smokeExtractionSystems()->orderBy('id')->get();

            if ($protocolSmokeSystems->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->smokeExtractionSystems()->exists()) {
                    foreach ($lastProtocol->smokeExtractionSystems as $prevSystem) {
                        $inventoryItem = SmokeExtractionSystem::find($prevSystem->smoke_extraction_system_id);
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->smokeExtractionSystems()->create([
                                'smoke_extraction_system_id' => $prevSystem->smoke_extraction_system_id,
                                'central_type_name' => $prevSystem->central_type_name,
                                'location' => $prevSystem->location,
                                'detectors_count' => $prevSystem->detectors_count,
                                'buttons_count' => $prevSystem->buttons_count,
                                'vent_buttons_count' => $prevSystem->vent_buttons_count,
                                'air_inlet_count' => $prevSystem->air_inlet_count,
                                'smoke_exhaust_count' => $prevSystem->smoke_exhaust_count,
                                'result' => $prevSystem->result,
                                'notes' => $prevSystem->notes,
                            ]);
                        }
                    }
                } else {
                    $inventory = SmokeExtractionSystem::where('client_object_id', $protocol->clientObject->id)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->smokeExtractionSystems()->create([
                            'smoke_extraction_system_id' => $item->id,
                            'central_type_name' => $item->central_type_name,
                            'location' => $item->location,
                            'detectors_count' => $item->detectors_count,
                            'buttons_count' => $item->buttons_count,
                            'vent_buttons_count' => $item->vent_buttons_count,
                            'air_inlet_count' => $item->air_inlet_count,
                            'smoke_exhaust_count' => $item->smoke_exhaust_count,
                            'result' => 'positive',
                        ]);
                    }
                }
                $protocolSmokeSystems = $protocol->smokeExtractionSystems()->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolSmokeSystems'));
        }

        if ($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne') {
            $protocolLighting = $protocol->emergencyLightingDevices()->orderBy('id')->get();

            if ($protocolLighting->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->emergencyLightingDevices()->exists()) {
                    foreach ($lastProtocol->emergencyLightingDevices as $prevItem) {
                        $inventoryItem = EmergencyLightingDevice::find($prevItem->emergency_lighting_device_id);
                        if ($inventoryItem) {
                            $protocol->emergencyLightingDevices()->create([
                                'emergency_lighting_device_id' => $prevItem->emergency_lighting_device_id,
                                'type' => $prevItem->type,
                                'location' => $prevItem->location,
                                'check_startup_time' => $prevItem->check_startup_time,
                                'check_duration' => $prevItem->check_duration,
                                'result' => $prevItem->result,
                                'notes' => $prevItem->notes,
                            ]);
                        }
                    }
                } else {
                    $inventory = EmergencyLightingDevice::where('client_object_id', $protocol->clientObject->id)
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->emergencyLightingDevices()->create([
                            'emergency_lighting_device_id' => $item->id,
                            'type' => $item->type,
                            'location' => $item->location,
                            'result' => 'positive',
                        ]);
                    }
                }
                $protocolLighting = $protocol->emergencyLightingDevices()->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolLighting'));
        }

        if ($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu') {
            $protocolPwpDevices = $protocol->pwpDevices()->orderBy('system_number')->orderBy('id')->get();

            if ($protocolPwpDevices->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->pwpDevices()->exists()) {
                    foreach ($lastProtocol->pwpDevices as $prevItem) {
                        $inventoryItem = PwpDevice::find($prevItem->pwp_device_id);
                        if ($inventoryItem) {
                            $protocol->pwpDevices()->create([
                                'pwp_device_id' => $prevItem->pwp_device_id,
                                'type' => $prevItem->type,
                                'location' => $prevItem->location,
                                'system_number' => $prevItem->system_number,
                                'check_access' => $prevItem->check_access,
                                'check_signage' => $prevItem->check_signage,
                                'check_condition' => $prevItem->check_condition,
                                'check_activation' => $prevItem->check_activation,
                                'result' => $prevItem->result,
                                'notes' => $prevItem->notes,
                            ]);
                        }
                    }
                } else {
                    $inventory = PwpDevice::where('client_object_id', $protocol->clientObject->id)
                        ->orderBy('system_number')
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->pwpDevices()->create([
                            'pwp_device_id' => $item->id,
                            'type' => $item->type,
                            'location' => $item->location,
                            'system_number' => $item->system_number,
                            'result' => 'positive',
                        ]);
                    }
                }
                $protocolPwpDevices = $protocol->pwpDevices()->orderBy('system_number')->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolPwpDevices'));
        }

        if ($protocol->system->slug === 'bramy-i-grodzie-przeciwpozarowe') {
            $protocolFireGateDevices = $protocol->fireGateDevices()->orderBy('system_number')->orderBy('id')->get();

            if ($protocolFireGateDevices->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->fireGateDevices()->exists()) {
                    foreach ($lastProtocol->fireGateDevices as $prevItem) {
                        $inventoryItem = FireGateDevice::find($prevItem->fire_gate_device_id);
                        if ($inventoryItem) {
                            $protocol->fireGateDevices()->create([
                                'fire_gate_device_id' => $prevItem->fire_gate_device_id,
                                'type' => $prevItem->type,
                                'system_number' => $prevItem->system_number,
                                'location' => $prevItem->location,
                                'gate_type' => $prevItem->gate_type,
                                'fire_resistance_class' => $prevItem->fire_resistance_class,
                                'manufacturer' => $prevItem->manufacturer,
                                'model' => $prevItem->model,
                                'check_detectors' => $prevItem->check_detectors,
                                'check_buttons' => $prevItem->check_buttons,
                                'check_signalers' => $prevItem->check_signalers,
                                'check_holding_mechanism' => $prevItem->check_holding_mechanism,
                                'check_drive' => $prevItem->check_drive,
                                'check_counterweight' => $prevItem->check_counterweight,
                                'check_magnetic_clutch' => $prevItem->check_magnetic_clutch,
                                'check_test_button' => $prevItem->check_test_button,
                                'battery_date' => $prevItem->battery_date,
                                'result' => $prevItem->result,
                                'notes' => $prevItem->notes,
                            ]);
                        }
                    }
                } else {
                    $inventory = FireGateDevice::where('client_object_id', $protocol->clientObject->id)
                        ->orderBy('system_number')
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($inventory as $item) {
                        $protocol->fireGateDevices()->create([
                            'fire_gate_device_id' => $item->id,
                            'type' => $item->type,
                            'system_number' => $item->system_number,
                            'location' => $item->location,
                            'gate_type' => $item->gate_type,
                            'fire_resistance_class' => $item->fire_resistance_class,
                            'manufacturer' => $item->manufacturer,
                            'model' => $item->model,
                            'result' => 'positive',
                        ]);
                    }
                }
                $protocolFireGateDevices = $protocol->fireGateDevices()->orderBy('system_number')->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolFireGateDevices'));
        }

        if ($protocol->system->slug === 'wentylacja') {
            $protocolDistributors = $protocol->ventilationDistributors()->orderBy('id')->get();
            $protocolFans = $protocol->ventilationFans()->orderBy('id')->get();

            if ($protocolDistributors->isEmpty() && $protocolFans->isEmpty()) {
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                // 1. Rozdzielnice
                if ($lastProtocol && $lastProtocol->ventilationDistributors()->exists()) {
                    foreach ($lastProtocol->ventilationDistributors as $prevItem) {
                        $inventoryItem = VentilationDistributor::find($prevItem->ventilation_distributor_id);
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->ventilationDistributors()->create([
                                'ventilation_distributor_id' => $prevItem->ventilation_distributor_id,
                                'name' => $prevItem->name,
                                'location' => $prevItem->location,
                            ]);
                        }
                    }
                } else {
                    $inventory = VentilationDistributor::where('client_object_id', $protocol->clientObject->id)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();
                    foreach ($inventory as $item) {
                        $protocol->ventilationDistributors()->create([
                            'ventilation_distributor_id' => $item->id,
                            'name' => $item->name,
                            'location' => $item->location,
                        ]);
                    }
                }

                // 2. Wentylatory
                if ($lastProtocol && $lastProtocol->ventilationFans()->exists()) {
                    foreach ($lastProtocol->ventilationFans as $prevItem) {
                        $inventoryItem = VentilationFan::find($prevItem->ventilation_fan_id);
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->ventilationFans()->create([
                                'ventilation_fan_id' => $prevItem->ventilation_fan_id,
                                'name' => $prevItem->name,
                                'location' => $prevItem->location,
                            ]);
                        }
                    }
                } else {
                    $inventory = VentilationFan::where('client_object_id', $protocol->clientObject->id)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();
                    foreach ($inventory as $item) {
                        $protocol->ventilationFans()->create([
                            'ventilation_fan_id' => $item->id,
                            'name' => $item->name,
                            'location' => $item->location,
                        ]);
                    }
                }

                $protocolDistributors = $protocol->ventilationDistributors()->orderBy('id')->get();
                $protocolFans = $protocol->ventilationFans()->orderBy('id')->get();
            }

            return view('protocols.step2', compact('protocol', 'protocolDistributors', 'protocolFans'));
        }

        return view('protocols.step2', compact('protocol'));
    }

    public function storeStep2(Request $request, Protocol $protocol)
    {
        // Krok 2 dla gaśnic to teraz tylko zatwierdzenie listy (ewentualne zmiany w inwentarzu robimy livewirem lub osobnym mechanizmem,
        // ale w tym uproszczonym flow po prostu przechodzimy dalej).
        // Ewentualnie tutaj można by obsłużyć dodawanie/usuwanie pozycji z protokołu, ale
        // skoro user chce "tworzyć listę", to zakładamy że lista jest już przygotowana w widoku (np. przez Livewire managera lub po prostu podgląd).

        // Przekierowanie do Kroku 3 (Stany)
        return redirect()->route('protocols.step3', $protocol);
    }

    /**
     * Krok 3: Uzupełnianie stanów
     */
    public function step3(Protocol $protocol)
    {
        // Automatyczne pobranie uwag ogólnych z ostatniego protokołu, jeśli jeszcze ich nie ma
        if (!isset($protocol->data['final_notes'])) {
            $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                ->where('system_id', $protocol->system->id)
                ->where('id', '<', $protocol->id)
                ->orderBy('date', 'desc')
                ->first();

            if ($lastProtocol && isset($lastProtocol->data['final_notes'])) {
                $data = $protocol->data ?? [];
                $data['final_notes'] = $lastProtocol->data['final_notes'];
                $protocol->update(['data' => $data]);
            }
        }

        if ($protocol->system->slug === 'gasnice') {
            $protocolExtinguishers = $protocol->fireExtinguishers()->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolExtinguishers'));
        }

        if ($protocol->system->slug === 'drzwi-przeciwpozarowe') {
            $protocolDoors = $protocol->doors()->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolDoors'));
        }

        if ($protocol->system->slug === 'klapy-pozarowe') {
            $protocolDampers = $protocol->fireDampers()->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolDampers'));
        }

        if ($protocol->system->slug === 'system-oddymiania') {
            $protocolSmokeSystems = $protocol->smokeExtractionSystems()->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolSmokeSystems'));
        }

        if ($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne') {
            $protocolLighting = $protocol->emergencyLightingDevices()->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolLighting'));
        }

        if ($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu') {
            $protocolPwpDevices = $protocol->pwpDevices()->orderBy('system_number')->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolPwpDevices'));
        }

        if ($protocol->system->slug === 'bramy-i-grodzie-przeciwpozarowe') {
            $protocolFireGateDevices = $protocol->fireGateDevices()->orderBy('system_number')->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolFireGateDevices'));
        }

        if ($protocol->system->slug === 'wentylacja') {
            $protocolDistributors = $protocol->ventilationDistributors()->orderBy('id')->get();
            $protocolFans = $protocol->ventilationFans()->orderBy('id')->get();
            return view('protocols.step3', compact('protocol', 'protocolDistributors', 'protocolFans'));
        }

        // Dla innych systemów (placeholder)
        return redirect()->route('protocols.preview', $protocol);
    }

    public function storeStep3(Request $request, Protocol $protocol)
    {
        if ($protocol->system->slug === 'gasnice') {
            $validated = $request->validate([
                'extinguishers' => 'array',
                'extinguishers.*.id' => 'required|exists:protocol_fire_extinguishers,id',
                'extinguishers.*.status' => 'required|in:legalizacja,remont,zlom,brak,po_remoncie,nowa',
                'extinguishers.*.next_service_year' => 'nullable|integer|min:2000|max:2100',
                'extinguishers.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['extinguishers'] as $data) {
                $extinguisher = ProtocolFireExtinguisher::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($extinguisher) {
                    $extinguisher->update([
                        'status' => $data['status'],
                        'next_service_year' => $data['next_service_year'],
                        'notes' => $data['notes'],
                    ]);

                    // Aktualizacja daty następnego remontu w inwentarzu
                    if ($extinguisher->fire_extinguisher_id) {
                        $inventory = FireExtinguisher::find($extinguisher->fire_extinguisher_id);
                        if ($inventory) {
                            $inventory->update(['next_service_year' => $data['next_service_year']]);
                        }
                    }
                }
            }
        }

        if ($protocol->system->slug === 'drzwi-przeciwpozarowe') {
            $validated = $request->validate([
                'doors' => 'array',
                'doors.*.id' => 'required|exists:protocol_doors,id',
                'doors.*.status' => 'required|in:sprawne,niesprawne',
                'doors.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['doors'] as $data) {
                $door = ProtocolDoor::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($door) {
                    $door->update([
                        'status' => $data['status'],
                        'notes' => $data['notes'],
                    ]);
                }
            }
        }

        if ($protocol->system->slug === 'klapy-pozarowe') {
            $validated = $request->validate([
                'dampers' => 'array',
                'dampers.*.id' => 'required|exists:protocol_fire_dampers,id',
                'dampers.*.check_optical' => 'nullable|boolean',
                'dampers.*.check_drive' => 'nullable|boolean',
                'dampers.*.check_mechanical' => 'nullable|boolean',
                'dampers.*.check_alarm' => 'nullable|boolean',
                'dampers.*.result' => 'required|in:positive,negative',
                'dampers.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['dampers'] as $data) {
                $damper = ProtocolFireDamper::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($damper) {
                    $damper->update([
                        'check_optical' => $data['check_optical'] ?? false,
                        'check_drive' => $data['check_drive'] ?? false,
                        'check_mechanical' => $data['check_mechanical'] ?? false,
                        'check_alarm' => $data['check_alarm'] ?? false,
                        'result' => $data['result'],
                        'notes' => $data['notes'],
                    ]);
                }
            }
        }

        if ($protocol->system->slug === 'system-oddymiania') {
            $validated = $request->validate([
                'systems' => 'array',
                'systems.*.id' => 'required|exists:protocol_smoke_extraction_systems,id',
                'systems.*.battery_date' => 'nullable|string',
                'systems.*.result' => 'required|in:positive,negative',
                'systems.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['systems'] as $data) {
                $system = ProtocolSmokeExtractionSystem::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($system) {
                    $system->update([
                        'battery_date' => $data['battery_date'],
                        'result' => $data['result'],
                        'notes' => $data['notes'],
                    ]);
                }
            }
        }

        if ($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne') {
            $validated = $request->validate([
                'items' => 'array',
                'items.*.id' => 'required|exists:protocol_emergency_lighting_devices,id',
                'items.*.check_startup_time' => 'nullable',
                'items.*.check_duration' => 'nullable',
                'items.*.result' => 'required|in:positive,negative',
                'items.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['items'] as $data) {
                $item = ProtocolEmergencyLightingDevice::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($item) {
                    $item->update([
                        'check_startup_time' => isset($data['check_startup_time']),
                        'check_duration' => isset($data['check_duration']),
                        'result' => $data['result'],
                        'notes' => $data['notes'],
                    ]);
                }
            }
        }

        if ($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu') {
            $validated = $request->validate([
                'items' => 'array',
                'items.*.id' => 'required|exists:protocol_pwp_devices,id',
                'items.*.check_access' => 'nullable',
                'items.*.check_signage' => 'nullable',
                'items.*.check_condition' => 'nullable',
                'items.*.check_activation' => 'nullable',
                'items.*.result' => 'required|in:positive,negative',
                'items.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['items'] as $data) {
                $item = ProtocolPwpDevice::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($item) {
                    $item->update([
                        'check_access' => isset($data['check_access']),
                        'check_signage' => isset($data['check_signage']),
                        'check_condition' => isset($data['check_condition']),
                        'check_activation' => isset($data['check_activation']),
                        'result' => $data['result'],
                        'notes' => $data['notes'],
                    ]);
                }
            }
        }

        if ($protocol->system->slug === 'bramy-i-grodzie-przeciwpozarowe') {
            $validated = $request->validate([
                'items' => 'array',
                'items.*.id' => 'required|exists:protocol_fire_gate_devices,id',
                'items.*.check_detectors' => 'nullable',
                'items.*.check_buttons' => 'nullable',
                'items.*.check_test_button' => 'nullable',
                'items.*.check_signalers' => 'nullable',
                'items.*.check_holding_mechanism' => 'nullable',
                'items.*.check_drive' => 'nullable',
                'items.*.check_counterweight' => 'nullable',
                'items.*.check_magnetic_clutch' => 'nullable',
                'items.*.battery_date' => 'nullable|string',
                'items.*.result' => 'required|in:positive,negative',
                'items.*.notes' => 'nullable|string',
            ]);

            foreach ($validated['items'] as $data) {
                $item = ProtocolFireGateDevice::where('id', $data['id'])
                    ->where('protocol_id', $protocol->id)
                    ->first();

                if ($item) {
                    $item->update([
                        'check_detectors' => isset($data['check_detectors']),
                        'check_buttons' => isset($data['check_buttons']),
                        'check_test_button' => isset($data['check_test_button']),
                        'check_signalers' => isset($data['check_signalers']),
                        'check_holding_mechanism' => isset($data['check_holding_mechanism']),
                        'check_drive' => isset($data['check_drive']),
                        'check_counterweight' => isset($data['check_counterweight']),
                        'check_magnetic_clutch' => isset($data['check_magnetic_clutch']),
                        'battery_date' => $data['battery_date'] ?? null,
                        'result' => $data['result'],
                        'notes' => $data['notes'],
                    ]);
                }
            }
        }

        if ($protocol->system->slug === 'wentylacja') {
            $validated = $request->validate([
                'distributors' => 'array',
                'distributors.*.id' => 'required|exists:protocol_ventilation_distributors,id',
                'distributors.*.check_visual_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_visual_notes' => 'nullable|string',
                'distributors.*.check_cables_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_cables_notes' => 'nullable|string',
                'distributors.*.check_devices_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_devices_notes' => 'nullable|string',
                'distributors.*.check_internal_cables_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_internal_cables_notes' => 'nullable|string',
                'distributors.*.check_main_switch_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_main_switch_notes' => 'nullable|string',
                'distributors.*.check_documentation_status' => 'required|in:1,0', // boolean as int from select/radio
                'distributors.*.check_documentation_notes' => 'nullable|string',
                'distributors.*.check_manual_controls_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_manual_controls_notes' => 'nullable|string',
                'distributors.*.check_optical_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_optical_notes' => 'nullable|string',
                'distributors.*.check_input_signals_status' => 'required|in:positive,negative,not_applicable',
                'distributors.*.check_input_signals_notes' => 'nullable|string',

                'fans' => 'array',
                'fans.*.id' => 'required|exists:protocol_ventilation_fans,id',
                'fans.*.check_alarm_level_2' => 'nullable',
                'fans.*.check_technical_condition' => 'required|in:good,bad',
                'fans.*.check_cables_condition' => 'required|in:good,bad',
                'fans.*.current_1' => 'nullable|string',
                'fans.*.current_2' => 'nullable|string',
                'fans.*.result' => 'required|in:positive,negative',
                'fans.*.notes' => 'nullable|string',
            ]);

            if (isset($validated['distributors'])) {
                foreach ($validated['distributors'] as $data) {
                    $item = ProtocolVentilationDistributor::where('id', $data['id'])
                        ->where('protocol_id', $protocol->id)
                        ->first();
                    if ($item) {
                        $item->update([
                            'check_visual_status' => $data['check_visual_status'],
                            'check_visual_notes' => $data['check_visual_notes'],
                            'check_cables_status' => $data['check_cables_status'],
                            'check_cables_notes' => $data['check_cables_notes'],
                            'check_devices_status' => $data['check_devices_status'],
                            'check_devices_notes' => $data['check_devices_notes'],
                            'check_internal_cables_status' => $data['check_internal_cables_status'],
                            'check_internal_cables_notes' => $data['check_internal_cables_notes'],
                            'check_main_switch_status' => $data['check_main_switch_status'],
                            'check_main_switch_notes' => $data['check_main_switch_notes'],
                            'check_documentation_status' => (bool)$data['check_documentation_status'],
                            'check_documentation_notes' => $data['check_documentation_notes'],
                            'check_manual_controls_status' => $data['check_manual_controls_status'],
                            'check_manual_controls_notes' => $data['check_manual_controls_notes'],
                            'check_optical_status' => $data['check_optical_status'],
                            'check_optical_notes' => $data['check_optical_notes'],
                            'check_input_signals_status' => $data['check_input_signals_status'],
                            'check_input_signals_notes' => $data['check_input_signals_notes'],
                        ]);
                    }
                }
            }

            if (isset($validated['fans'])) {
                foreach ($validated['fans'] as $data) {
                    $item = ProtocolVentilationFan::where('id', $data['id'])
                        ->where('protocol_id', $protocol->id)
                        ->first();
                    if ($item) {
                        $item->update([
                            'check_alarm_level_2' => isset($data['check_alarm_level_2']),
                            'check_technical_condition' => $data['check_technical_condition'],
                            'check_cables_condition' => $data['check_cables_condition'],
                            'current_1' => $data['current_1'],
                            'current_2' => $data['current_2'],
                            'result' => $data['result'],
                            'notes' => $data['notes'],
                        ]);
                    }
                }
            }
        }

        // Zapisz uwagi ogólne (wspólne dla wszystkich systemów)
        if ($request->has('final_notes')) {
            $data = $protocol->data ?? [];
            $data['final_notes'] = $request->input('final_notes');
            $protocol->update(['data' => $data]);
        }

        return redirect()->route('protocols.preview', $protocol);
    }

    /**
     * Krok 3: Podgląd
     */
    public function preview(Protocol $protocol)
    {
        $template = ProtocolTemplate::find($protocol->data['template_id']);

        // Przygotuj dane do podglądu (te same co do PDF, jeśli to gaśnice)
        $previewData = [];

        if ($protocol->system->slug === 'gasnice') {
            $extinguishers = $protocol->fireExtinguishers()->orderBy('id')->get();

            // Statystyki
            $stats = [];
            $statuses = [
                'legalizacja' => 'Legalizacja',
                'remont' => 'Do remontu',
                'zlom' => 'Do złomowania',
                'brak' => 'Brak',
                'po_remoncie' => 'Po remoncie',
                'nowa' => 'Nowa'
            ];

            foreach ($extinguishers as $ext) {
                $type = $ext->type_name ?? 'Inny';
                if (!isset($stats[$type])) {
                    $stats[$type] = array_fill_keys(array_keys($statuses), 0);
                    $stats[$type]['total'] = 0;
                }
                $stats[$type][$ext->status]++;
                $stats[$type]['total']++;
            }

            // Sumy
            $totals = array_fill_keys(array_keys($statuses), 0);
            $totals['total'] = 0;
            foreach ($stats as $typeStats) {
                foreach ($typeStats as $key => $val) {
                    // Pomijamy klucz 'total' w wewnętrznej pętli, bo on jest już zliczony w $stats
                    if ($key !== 'total') {
                         $totals[$key] += $val;
                    }
                }
                $totals['total'] += $typeStats['total'];
            }

            $previewData = compact('extinguishers', 'stats', 'statuses', 'totals');
        }

        if ($protocol->system->slug === 'drzwi-przeciwpozarowe') {
            $doors = $protocol->doors()->orderBy('id')->get();

            $stats = [];
            $statuses = [
                'sprawne' => 'Sprawne',
                'niesprawne' => 'Niesprawne'
            ];

            foreach ($doors as $door) {
                $type = $door->resistance_class ?? 'Brak klasy';
                if (!isset($stats[$type])) {
                    $stats[$type] = array_fill_keys(array_keys($statuses), 0);
                    $stats[$type]['total'] = 0;
                }
                $stats[$type][$door->status]++;
                $stats[$type]['total']++;
            }

            $totals = array_fill_keys(array_keys($statuses), 0);
            $totals['total'] = 0;
            foreach ($stats as $typeStats) {
                foreach ($typeStats as $key => $val) {
                    if ($key !== 'total') {
                         $totals[$key] += $val;
                    }
                }
                $totals['total'] += $typeStats['total'];
            }

            $previewData = compact('doors', 'stats', 'statuses', 'totals');
        }

        if ($protocol->system->slug === 'klapy-pozarowe') {
            $dampers = $protocol->fireDampers()->orderBy('id')->get();

            $stats = [
                'total' => $dampers->count(),
                'positive' => $dampers->where('result', 'positive')->count(),
                'negative' => $dampers->where('result', 'negative')->count(),
            ];

            $previewData = compact('dampers', 'stats');
        }

        if ($protocol->system->slug === 'system-oddymiania') {
            $smokeSystems = $protocol->smokeExtractionSystems()->orderBy('id')->get();

            $stats = [
                'total' => $smokeSystems->count(),
                'positive' => $smokeSystems->where('result', 'positive')->count(),
                'negative' => $smokeSystems->where('result', 'negative')->count(),
            ];

            $previewData = compact('smokeSystems', 'stats');
        }

        if ($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne') {
            $lightingDevices = $protocol->emergencyLightingDevices()->orderBy('id')->get();

            $stats = [];
            $totals = ['total' => 0, 'positive' => 0, 'negative' => 0];

            // Grupuj według typu
            $grouped = $lightingDevices->groupBy('type');

            foreach ($grouped as $type => $items) {
                $count = $items->count();
                $positive = $items->where('result', 'positive')->count();
                $negative = $items->where('result', 'negative')->count();

                $stats[$type] = [
                    'total' => $count,
                    'positive' => $positive,
                    'negative' => $negative,
                ];

                $totals['total'] += $count;
                $totals['positive'] += $positive;
                $totals['negative'] += $negative;
            }

            $previewData = compact('lightingDevices', 'stats', 'totals');
        }

        if ($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu') {
            $pwpDevices = $protocol->pwpDevices()->orderBy('system_number')->orderBy('id')->get();

            $stats = [];
            $totals = ['total' => 0, 'positive' => 0, 'negative' => 0];

            // Liczymy ilość systemów (unikalne system_number)
            $uniqueSystems = $pwpDevices->pluck('system_number')->unique();
            $totals['total'] = $uniqueSystems->count();

            // Dla każdego systemu sprawdzamy czy WSZYSTKIE elementy są pozytywne
            foreach ($uniqueSystems as $sysNum) {
                $systemItems = $pwpDevices->where('system_number', $sysNum);
                $isPositive = $systemItems->every(fn($item) => $item->result === 'positive');

                if ($isPositive) {
                    $totals['positive']++;
                } else {
                    $totals['negative']++;
                }
            }

            // Dodatkowo statystyki per element (dla ciekawości, ale w podsumowaniu chcemy systemy)
            // W widoku użyjemy $totals['total'] jako ilość systemów

            $previewData = compact('pwpDevices', 'totals');
        }

        if ($protocol->system->slug === 'bramy-i-grodzie-przeciwpozarowe') {
            $fireGateDevices = $protocol->fireGateDevices()->orderBy('system_number')->orderBy('id')->get();

            $stats = [];
            $totals = ['gates' => 0, 'gates_positive' => 0, 'gates_negative' => 0, 'centrals' => 0, 'centrals_positive' => 0, 'centrals_negative' => 0];

            foreach ($fireGateDevices as $device) {
                if ($device->type === 'gate') {
                    $totals['gates']++;
                    if ($device->result === 'positive') $totals['gates_positive']++;
                    else $totals['gates_negative']++;
                } elseif ($device->type === 'central') {
                    $totals['centrals']++;
                    if ($device->result === 'positive') $totals['centrals_positive']++;
                    else $totals['centrals_negative']++;
                }
            }

            $previewData = compact('fireGateDevices', 'totals');
        }

        if ($protocol->system->slug === 'wentylacja') {
            $distributors = $protocol->ventilationDistributors()->orderBy('id')->get();
            $fans = $protocol->ventilationFans()->orderBy('id')->get();

            $stats = [
                'fans_total' => $fans->count(),
                'fans_positive' => $fans->where('result', 'positive')->count(),
                'fans_negative' => $fans->where('result', 'negative')->count(),
                'distributors_count' => $distributors->count(),
            ];

            $previewData = compact('distributors', 'fans', 'stats');
        }

        return view('protocols.preview', compact('protocol', 'template') + $previewData);
    }

    /**
     * Krok 4: Generowanie PDF
     */
    public function pdf(Protocol $protocol)
    {
        $template = ProtocolTemplate::find($protocol->data['template_id']);
        $company = CompanySetting::first();

        $pdf = Pdf::loadView('protocols.pdf', compact('protocol', 'template', 'company'));

        $filename = sprintf(
            '%s-%s-%s-%s.pdf',
            $protocol->date->format('m-Y'),
            Str::slug($protocol->clientObject->name),
            Str::slug($protocol->system->name),
            str_replace('/', '-', $protocol->number)
        );

        return $pdf->stream($filename);
    }

    public function downloadPdf(Protocol $protocol)
    {
        $template = ProtocolTemplate::find($protocol->data['template_id']);
        $company = CompanySetting::first();

        $pdf = Pdf::loadView('protocols.pdf', compact('protocol', 'template', 'company'));

        $filename = sprintf(
            '%s-%s-%s-%s.pdf',
            $protocol->date->format('m-Y'),
            Str::slug($protocol->clientObject->name),
            Str::slug($protocol->system->name),
            str_replace('/', '-', $protocol->number)
        );

        return $pdf->download($filename);
    }

    public function edit(Protocol $protocol)
    {
        $object = $protocol->clientObject;
        $system = $protocol->system;
        $templates = $system->protocolTemplates()->orderBy('is_default', 'desc')->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('protocols.edit', compact('protocol', 'object', 'system', 'templates', 'users'));
    }

    public function update(Request $request, Protocol $protocol)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:protocol_templates,id',
            'date' => 'required|date',
            'next_date_option' => $protocol->system->has_periodic_review ? 'required|in:3,6,12,none' : 'nullable',
            'performer_id' => 'required|exists:users,id',
        ]);

        $nextDate = null;
        if ($protocol->system->has_periodic_review && $validated['next_date_option'] !== 'none') {
            $nextDate = Carbon::parse($validated['date'])->addMonths((int)$validated['next_date_option']);
        }

        $data = $protocol->data;
        $data['template_id'] = $validated['template_id'];

        $protocol->update([
            'date' => $validated['date'],
            'next_date' => $nextDate,
            'performer_id' => $validated['performer_id'],
            'data' => $data,
        ]);

        // Po zapisaniu edycji 1 kroku, przejdź do kroku 2
        return redirect()->route('protocols.step2', $protocol);
    }
}
