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

        $typeName = '';
        if ($this->type_id) {
            $type = FireExtinguisherType::find($this->type_id);
            $typeName = $type ? $type->name : $this->custom_type;
        } else {
            $typeName = $this->custom_type;
        }

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
                        'type_id' => $this->type_id ?: null,
                        'custom_type' => $this->type_id ? null : $typeName,
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
                'type_id' => $this->type_id ?: null,
                'custom_type' => $this->type_id ? null : $typeName,
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
