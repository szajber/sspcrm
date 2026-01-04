<div>
    <div class="flex justify-between items-center mb-4">
        <div>
            <h4 class="text-md font-semibold text-gray-700">{{ __('Lista Gaśnic w Protokole') }}</h4>
            <p class="text-sm text-gray-500">{{ __('Weryfikacja inwentaryzacji przed przystąpieniem do przeglądu.') }}</p>
        </div>
        <x-button type="button" wire:click="openModal">
            {{ __('+ Dodaj Gaśnicę') }}
        </x-button>
    </div>

    @if($extinguishers->isEmpty())
        <div class="text-center py-4 text-gray-500 bg-gray-50 rounded">
            {{ __('Brak gaśnic w tym protokole. Dodaj je, aby kontynuować.') }}
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Akcje') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($extinguishers as $index => $extinguisher)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $extinguisher->type_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $extinguisher->location }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <div class="flex flex-col space-y-1">
                                        <button wire:click="moveUp({{ $extinguisher->id }})" class="text-gray-500 hover:text-gray-700" title="{{ __('Przesuń w górę') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button wire:click="moveUp10({{ $extinguisher->id }})" class="text-xs text-gray-400 hover:text-gray-600" title="{{ __('Przesuń o 10 w górę') }}">
                                            +10
                                        </button>
                                    </div>
                                    <div class="flex flex-col space-y-1">
                                        <button wire:click="moveDown({{ $extinguisher->id }})" class="text-gray-500 hover:text-gray-700" title="{{ __('Przesuń w dół') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button wire:click="moveDown10({{ $extinguisher->id }})" class="text-xs text-gray-400 hover:text-gray-600" title="{{ __('Przesuń o 10 w dół') }}">
                                            -10
                                        </button>
                                    </div>
                                    <button wire:click="clone({{ $extinguisher->id }})" class="text-green-600 hover:text-green-900 ml-2">
                                        {{ __('Klonuj') }}
                                    </button>
                                    <button wire:click="edit({{ $extinguisher->id }})" class="text-indigo-600 hover:text-indigo-900 ml-2">
                                        {{ __('Edytuj') }}
                                    </button>
                                    <button wire:click="delete({{ $extinguisher->id }})" class="text-red-600 hover:text-red-900">
                                        {{ __('Usuń') }}
                                    </button>
                                </div>
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

                <p class="text-xs text-gray-500 mt-2">
                    {{ __('Uwaga: Zmiany tutaj zostaną zapisane w protokole oraz zaktualizują inwentaryzację obiektu.') }}
                </p>
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
