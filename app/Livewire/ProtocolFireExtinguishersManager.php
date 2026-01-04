<?php

namespace App\Livewire;

use App\Models\Protocol;
use App\Models\ProtocolFireExtinguisher;
use App\Models\FireExtinguisher;
use App\Models\FireExtinguisherType;
use Livewire\Component;

class ProtocolFireExtinguishersManager extends Component
{
    public Protocol $protocol;
    public $types;

    // Form properties
    public $editingId = null;
    public $showModal = false;
    public $type_id;
    public $custom_type;
    public $location;
    
    public function mount(Protocol $protocol)
    {
        $this->protocol = $protocol;
        $this->types = FireExtinguisherType::orderBy('name')->get();
    }

    public function render()
    {
        // Pobieramy gaśnice posortowane według kolejności z inwentarza
        $extinguishers = ProtocolFireExtinguisher::where('protocol_id', $this->protocol->id)
            ->leftJoin('fire_extinguishers', 'protocol_fire_extinguishers.fire_extinguisher_id', '=', 'fire_extinguishers.id')
            ->select('protocol_fire_extinguishers.*')
            ->orderBy('fire_extinguishers.sort_order')
            ->orderBy('protocol_fire_extinguishers.id')
            ->get();

        return view('livewire.protocol-fire-extinguishers-manager', [
            'extinguishers' => $extinguishers
        ]);
    }

    public function moveUp($id)
    {
        $current = ProtocolFireExtinguisher::find($id);
        if (!$current || !$current->fire_extinguisher_id) return;

        $currentInventory = FireExtinguisher::find($current->fire_extinguisher_id);
        if (!$currentInventory) return;

        // Znajdź poprzedni element w tym samym obiekcie
        $previousInventory = FireExtinguisher::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '<', $currentInventory->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousInventory) {
            $tempOrder = $currentInventory->sort_order;
            $currentInventory->update(['sort_order' => $previousInventory->sort_order]);
            $previousInventory->update(['sort_order' => $tempOrder]);
        }
    }

    public function moveDown($id)
    {
        $current = ProtocolFireExtinguisher::find($id);
        if (!$current || !$current->fire_extinguisher_id) return;

        $currentInventory = FireExtinguisher::find($current->fire_extinguisher_id);
        if (!$currentInventory) return;

        // Znajdź następny element w tym samym obiekcie
        $nextInventory = FireExtinguisher::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextInventory) {
            $tempOrder = $currentInventory->sort_order;
            $currentInventory->update(['sort_order' => $nextInventory->sort_order]);
            $nextInventory->update(['sort_order' => $tempOrder]);
        }
    }

    public function moveUp10($id)
    {
        $current = ProtocolFireExtinguisher::find($id);
        if (!$current || !$current->fire_extinguisher_id) return;

        $currentInventory = FireExtinguisher::find($current->fire_extinguisher_id);
        if (!$currentInventory) return;

        // Znajdź 10 wcześniejszych elementów
        $previousInventories = FireExtinguisher::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '<', $currentInventory->sort_order)
            ->orderBy('sort_order', 'desc')
            ->take(10)
            ->get();

        if ($previousInventories->isEmpty()) return;

        $targetInventory = $previousInventories->last();
        
        // Przesuń target i wszystko po nim (do current) w dół o 1
        // A current wstaw na miejsce target
        
        // Prostszą metodą przy sort_order liczbowym unikalnym jest zamiana miejscami
        // ale to może być mylące przy skoku o 10.
        // Najbezpieczniej jest wykonać serię zamian lub przenumerować.
        // Dla uproszczenia UX, zróbmy pętlę zamian.
        
        $steps = $previousInventories->count();
        $tempOrder = $currentInventory->sort_order;
        
        // Pobierz wszystkie elementy pomiędzy (włącznie z target i current)
        $range = FireExtinguisher::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>=', $targetInventory->sort_order)
            ->where('sort_order', '<=', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->get();
            
        // Logika: element ostatni (current) idzie na początek
        // reszta przesuwa się o 1 w dół
        
        if ($range->count() > 1) {
            $currentVal = $range->last();
            $targetOrder = $range->first()->sort_order;
            
            // Przesuwamy wszystkie oprócz ostatniego o jeden w górę (w sensie wartości sort_order)
            // Czekaj, sort_order rośnie w dół listy.
            // Więc żeby przesunąć w dół listy, zwiększamy sort_order.
            
            // Chcemy przenieść current (ostatni w range) na miejsce pierwszego w range.
            // A pozostałe przesunąć o 1 w dół (zwiększyć sort_order).
            
            // Najpierw ustawiamy current na tymczasową wartość spoza zakresu żeby nie było kolizji unique (jeśli jest)
            // Ale tutaj nie ma unique constraint w bazie na sort_order, więc możemy nadpisywać.
            
            // Pobieramy IDki w kolejności
            $ids = $range->pluck('id')->toArray();
            $orders = $range->pluck('sort_order')->toArray();
            
            // Ostatni ID (current)
            $currentId = array_pop($ids);
            
            // Wstawiamy go na początek
            array_unshift($ids, $currentId);
            
            // Teraz mamy nową kolejność IDków, przypisujemy im stare ordery
            foreach ($ids as $index => $id) {
                FireExtinguisher::where('id', $id)->update(['sort_order' => $orders[$index]]);
            }
        }
    }

    public function moveDown10($id)
    {
        $current = ProtocolFireExtinguisher::find($id);
        if (!$current || !$current->fire_extinguisher_id) return;

        $currentInventory = FireExtinguisher::find($current->fire_extinguisher_id);
        if (!$currentInventory) return;

        // Znajdź 10 następnych elementów
        $nextInventories = FireExtinguisher::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>', $currentInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->take(10)
            ->get();

        if ($nextInventories->isEmpty()) return;

        $targetInventory = $nextInventories->last();
        
        $range = FireExtinguisher::where('client_object_id', $currentInventory->client_object_id)
            ->where('sort_order', '>=', $currentInventory->sort_order)
            ->where('sort_order', '<=', $targetInventory->sort_order)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($range->count() > 1) {
            // Logika: element pierwszy (current) idzie na koniec
            // reszta przesuwa się o 1 w górę (zmniejsza sort_order)
            
            $ids = $range->pluck('id')->toArray();
            $orders = $range->pluck('sort_order')->toArray();
            
            // Pierwszy ID (current)
            $currentId = array_shift($ids);
            
            // Wstawiamy go na koniec
            array_push($ids, $currentId);
            
            // Aktualizacja
            foreach ($ids as $index => $id) {
                FireExtinguisher::where('id', $id)->update(['sort_order' => $orders[$index]]);
            }
        }
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
        $this->type_id = '';
        $this->custom_type = '';
        $this->location = '';
    }

    public function edit($id)
    {
        $extinguisher = ProtocolFireExtinguisher::find($id);
        if ($extinguisher) {
            $this->editingId = $id;
            // Tutaj musimy "odgadnąć" type_id na podstawie type_name jeśli to możliwe,
            // ale ponieważ w ProtocolFireExtinguisher zapisujemy tylko nazwę, to może być trudne.
            // Dla uproszczenia w tym widoku pozwalamy edytować nazwę (custom_type) lub wybrać nowy typ.
            
            // Próbujemy dopasować typ po nazwie
            $type = FireExtinguisherType::where('name', $extinguisher->type_name)->first();
            
            $this->type_id = $type ? $type->id : '';
            $this->custom_type = $type ? '' : $extinguisher->type_name;
            $this->location = $extinguisher->location;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'type_id' => 'nullable|exists:fire_extinguisher_types,id',
            'custom_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // Automatyczne dodawanie nowego typu do słownika
        if (empty($this->type_id) && !empty($this->custom_type)) {
            // Sprawdź czy taki typ już istnieje (case-insensitive)
            $existingType = FireExtinguisherType::where('name', $this->custom_type)->first();

            if ($existingType) {
                $this->type_id = $existingType->id;
            } else {
                // Utwórz nowy typ
                $newType = FireExtinguisherType::create([
                    'name' => $this->custom_type,
                    'description' => 'Dodano automatycznie podczas tworzenia protokołu.'
                ]);
                $this->type_id = $newType->id;
                
                // Odśwież listę typów w komponencie
                $this->types = FireExtinguisherType::orderBy('name')->get();
            }
        }

        $typeName = '';
        if ($this->type_id) {
            $type = FireExtinguisherType::find($this->type_id);
            $typeName = $type ? $type->name : $this->custom_type;
        } else {
            $typeName = $this->custom_type;
        }

        // Jeśli udało się ustalić type_id, to czyścimy custom_type dla inwentarza (bo preferujemy relację)
        // Ale dla ProtocolFireExtinguisher zapisujemy type_name tak czy siak.
        $inventoryCustomType = $this->type_id ? null : $this->custom_type;
        $inventoryTypeId = $this->type_id ?: null;

        if ($this->editingId) {
            // Edycja istniejącej pozycji w protokole
            $extinguisher = ProtocolFireExtinguisher::find($this->editingId);
            $extinguisher->update([
                'type_name' => $typeName,
                'location' => $this->location,
            ]);
            
            // Opcjonalnie: Aktualizacja w inwentarzu (jeśli powiązana)
            if ($extinguisher->fire_extinguisher_id) {
                $inventory = FireExtinguisher::find($extinguisher->fire_extinguisher_id);
                if ($inventory) {
                    $inventory->update([
                        'type_id' => $inventoryTypeId,
                        'custom_type' => $inventoryCustomType,
                        'location' => $this->location,
                    ]);
                }
            }
        } else {
            // Dodawanie nowej pozycji
            // 1. Dodajemy do inwentarza (żeby była w przyszłości)
            $maxOrder = FireExtinguisher::where('client_object_id', $this->protocol->clientObject->id)
                ->max('sort_order') ?? 0;
            
            // Przy dodawaniu nowej gaśnicy nie mamy jeszcze roku następnego remontu (będzie dodany w kroku 3)
            // Ale musimy dodać ją do inwentarza. Domyślnie rok nast. remontu = null
            
            $inventory = FireExtinguisher::create([
                'client_object_id' => $this->protocol->clientObject->id,
                'type_id' => $inventoryTypeId,
                'custom_type' => $inventoryCustomType,
                'location' => $this->location,
                'sort_order' => $maxOrder + 1,
            ]);

            // 2. Dodajemy do protokołu
            $this->protocol->fireExtinguishers()->create([
                'fire_extinguisher_id' => $inventory->id,
                'type_name' => $typeName,
                'location' => $this->location,
                'status' => 'legalizacja', // Domyślny status
            ]);
        }

        $this->closeModal();
    }

    public function clone($id)
    {
        $original = ProtocolFireExtinguisher::find($id);
        if ($original) {
            $new = null;

            // Klonujemy w inwentarzu
            if ($original->fire_extinguisher_id) {
                $inventoryOrig = FireExtinguisher::find($original->fire_extinguisher_id);
                if ($inventoryOrig) {
                    $maxOrder = FireExtinguisher::where('client_object_id', $this->protocol->clientObject->id)
                        ->max('sort_order') ?? 0;

                    $inventoryNew = $inventoryOrig->replicate();
                    $inventoryNew->sort_order = $maxOrder + 1;
                    $inventoryNew->save();

                    // Dodajemy do protokołu
                    $new = $original->replicate();
                    $new->fire_extinguisher_id = $inventoryNew->id;
                    $new->save();
                }
            } else {
                 // Jeśli z jakiegoś powodu nie ma powiązania z inwentarzem (np. usunięta), to tylko w protokole
                 $new = $original->replicate();
                 $new->save();
            }

            // Otwórz edycję nowej gaśnicy
            if ($new) {
                $this->edit($new->id);
            }
        }
    }

    public function delete($id)
    {
        $extinguisher = ProtocolFireExtinguisher::find($id);
        if ($extinguisher) {
            // Usuwamy z protokołu
            $extinguisher->delete();

            // Opcjonalnie: Ukrywamy w inwentarzu (soft delete logiczne)
            // Decyzja: Czy usunięcie z protokołu ma usuwać z obiektu?
            // Zwykle tak, jeśli to jest "zarządzanie listą".
            if ($extinguisher->fire_extinguisher_id) {
                $inventory = FireExtinguisher::find($extinguisher->fire_extinguisher_id);
                if ($inventory) {
                    $inventory->update(['is_active' => false]);
                }
            }
        }
    }
}
