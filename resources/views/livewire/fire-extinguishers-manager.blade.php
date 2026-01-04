<div>
    <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-semibold text-gray-700">{{ __('Lista Gaśnic') }}</h4>
        <x-button wire:click="openModal">
            {{ __('+ Dodaj Gaśnicę') }}
        </x-button>
    </div>

    @if($extinguishers->isEmpty())
        <div class="text-center py-4 text-gray-500 bg-gray-50 rounded">
            {{ __('Brak zdefiniowanych gaśnic dla tego obiektu.') }}
        </div>
    @else
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                            #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Typ') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Lokalizacja') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Akcje') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" wire:sortable="updateOrder">
                    @foreach($extinguishers as $extinguisher)
                        <tr wire:sortable.item="{{ $extinguisher->id }}" wire:key="extinguisher-{{ $extinguisher->id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 cursor-move" wire:sortable.handle>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $extinguisher->type_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $extinguisher->location }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="clone({{ $extinguisher->id }})" class="text-green-600 hover:text-green-900 mr-3" title="{{ __('Klonuj') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                </button>
                                <button wire:click="edit({{ $extinguisher->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3" title="{{ __('Edytuj') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button wire:click="delete({{ $extinguisher->id }})" class="text-red-600 hover:text-red-900" title="{{ __('Usuń') }}" onclick="confirm('{{ __('Czy na pewno chcesz usunąć tę gaśnicę?') }}') || event.stopImmediatePropagation()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Modal -->
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ $editingId ? __('Edytuj Gaśnicę') : __('Dodaj Gaśnicę') }}
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 gap-6">
                <!-- Typ z listy -->
                <div>
                    <x-label for="type_id" value="{{ __('Typ Gaśnicy (z listy)') }}" />
                    <select wire:model="type_id" id="type_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                        <option value="">{{ __('Wybierz typ...') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="type_id" class="mt-2" />
                </div>

                <!-- LUB Typ niestandardowy -->
                <div>
                    <x-label for="custom_type" value="{{ __('LUB Typ Niestandardowy (jeśli brak na liście)') }}" />
                    <x-input wire:model="custom_type" id="custom_type" type="text" class="block mt-1 w-full" placeholder="np. Inny typ" />
                    <x-input-error for="custom_type" class="mt-2" />
                </div>

                <!-- Lokalizacja -->
                <div>
                    <x-label for="location" value="{{ __('Lokalizacja') }}" />
                    <x-input wire:model="location" id="location" type="text" class="block mt-1 w-full" />
                    <x-input-error for="location" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeModal" wire:loading.attr="disabled">
                {{ __('Anuluj') }}
            </x-secondary-button>

            <x-button class="ml-2" wire:click="save" wire:loading.attr="disabled">
                {{ $editingId ? __('Zapisz zmiany') : __('Dodaj') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
