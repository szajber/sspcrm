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
use Illuminate\Support\Str;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProtocolController extends Controller
{
    /**
     * Krok 1: Wybór szablonu, dat i wykonawcy
     */
    public function index(ClientObject $object, System $system)
    {
        $protocols = Protocol::where('client_object_id', $object->id)
            ->where('system_id', $system->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('protocols.index', compact('object', 'system', 'protocols'));
    }

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
        if ($protocol->system->slug === 'gasnice') {
            // Logika inicjalizacji listy gaśnic (pobranie z inwentarza lub poprzedniego protokołu)
            // To już mamy zaimplementowane w poprzednim kroku, ale teraz to jest KROK 2
            // Celem jest tylko wyświetlenie listy i ew. dodanie/usunięcie pozycji
            // Właściwe badanie stanu przenosimy do Kroku 3

            $protocolExtinguishers = $protocol->fireExtinguishers()->orderBy('id')->get();

            if ($protocolExtinguishers->isEmpty()) {
                // ... (Logika kopiowania jak wcześniej) ...
                $lastProtocol = Protocol::where('client_object_id', $protocol->clientObject->id)
                    ->where('system_id', $protocol->system->id)
                    ->where('id', '<', $protocol->id)
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastProtocol && $lastProtocol->fireExtinguishers()->exists()) {
                    foreach ($lastProtocol->fireExtinguishers as $prevExtinguisher) {
                        $inventoryItem = FireExtinguisher::find($prevExtinguisher->fire_extinguisher_id);
                        if ($inventoryItem && $inventoryItem->is_active) {
                            $protocol->fireExtinguishers()->create([
                                'fire_extinguisher_id' => $prevExtinguisher->fire_extinguisher_id,
                                'type_name' => $prevExtinguisher->type_name,
                                'location' => $prevExtinguisher->location,
                                'status' => $prevExtinguisher->status,
                                'next_service_year' => $prevExtinguisher->next_service_year,
                                'notes' => $prevExtinguisher->notes,
                            ]);
                        }
                    }
                } else {
                    // Pobierz z inwentaryzacji obiektu
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
