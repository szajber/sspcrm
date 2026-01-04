<div>
    <div class="flex justify-between items-center mb-4">
        <div>
            <h4 class="text-md font-semibold text-gray-700">{{ __('Lista Systemów Oddymiania w Protokole') }}</h4>
            <p class="text-sm text-gray-500">{{ __('Weryfikacja inwentaryzacji przed przystąpieniem do przeglądu.') }}</p>
        </div>
        <x-button type="button" wire:click="openModal">
            {{ __('+ Dodaj System') }}
        </x-button>
    </div>

    @if($systems->isEmpty())
        <div class="text-center py-4 text-gray-500 bg-gray-50 rounded">
            {{ __('Brak systemów oddymiania w tym protokole. Dodaj je, aby kontynuować.') }}
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
                            {{ __('Centrala') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Lokalizacja') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Elementy systemu') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Akcje') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($systems as $index => $system)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $system->central_type_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $system->location }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <ul class="list-disc list-inside text-xs">
                                    <li>{{ __('Czujki:') }} {{ $system->detectors_count }}</li>
                                    <li>{{ __('Przyciski:') }} {{ $system->buttons_count }}</li>
                                    <li>{{ __('Przewietrzanie:') }} {{ $system->vent_buttons_count }}</li>
                                    <li>{{ __('Klapy/Went. napow.:') }} {{ $system->air_inlet_count }}</li>
                                    <li>{{ __('Klapy/Went. oddym.:') }} {{ $system->smoke_exhaust_count }}</li>
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <div class="flex flex-col space-y-1">
                                        <button wire:click="moveUp({{ $system->id }})" class="text-gray-500 hover:text-gray-700" title="{{ __('Przesuń w górę') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button wire:click="moveDown({{ $system->id }})" class="text-gray-500 hover:text-gray-700" title="{{ __('Przesuń w dół') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>
                                    <button wire:click="clone({{ $system->id }})" class="text-green-600 hover:text-green-900 ml-2">
                                        {{ __('Klonuj') }}
                                    </button>
                                    <button wire:click="edit({{ $system->id }})" class="text-indigo-600 hover:text-indigo-900 ml-2">
                                        {{ __('Edytuj') }}
                                    </button>
                                    <button wire:click="delete({{ $system->id }})" class="text-red-600 hover:text-red-900">
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
            {{ $editingId ? __('Edytuj System') : __('Dodaj System') }}
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 gap-6">
                <!-- Typ centrali -->
                <div>
                    <x-label for="central_type_id" value="{{ __('Typ centrali') }}" />
                    <select wire:model.live="central_type_id" id="central_type_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                        <option value="">{{ __('Wybierz typ...') }}</option>
                        @foreach($availableTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="central_type_id" class="mt-2" />
                </div>

                <!-- Inny typ -->
                @if(empty($central_type_id))
                    <div>
                        <x-label for="custom_central_type" value="{{ __('Inny typ centrali (wpisz ręcznie)') }}" />
                        <x-input wire:model="custom_central_type" id="custom_central_type" type="text" class="block mt-1 w-full" placeholder="np. UCS 6000 (zostanie dodana do listy)" />
                        <x-input-error for="custom_central_type" class="mt-2" />
                    </div>
                @endif

                <!-- Lokalizacja -->
                <div>
                    <x-label for="location" value="{{ __('Lokalizacja') }}" />
                    <x-input wire:model="location" id="location" type="text" class="block mt-1 w-full" />
                    <x-input-error for="location" class="mt-2" />
                </div>

                <!-- Parametry -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="detectors_count" value="{{ __('Ilość czujek / Sterowanie SSP') }}" />
                        <x-input wire:model="detectors_count" id="detectors_count" type="text" class="block mt-1 w-full" placeholder="np. 5 szt. lub Z systemu SSP" />
                        <x-input-error for="detectors_count" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="buttons_count" value="{{ __('Ilość przycisków RPO') }}" />
                        <x-input wire:model="buttons_count" id="buttons_count" type="number" min="0" class="block mt-1 w-full" />
                        <x-input-error for="buttons_count" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="vent_buttons_count" value="{{ __('Ilość przycisków przewietrzania') }}" />
                        <x-input wire:model="vent_buttons_count" id="vent_buttons_count" type="number" min="0" class="block mt-1 w-full" />
                        <x-input-error for="vent_buttons_count" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="air_inlet_count" value="{{ __('Klapy/Wentylatory napowietrzające') }}" />
                        <x-input wire:model="air_inlet_count" id="air_inlet_count" type="number" min="0" class="block mt-1 w-full" />
                        <x-input-error for="air_inlet_count" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="smoke_exhaust_count" value="{{ __('Klapy/Wentylatory oddymiające') }}" />
                        <x-input wire:model="smoke_exhaust_count" id="smoke_exhaust_count" type="number" min="0" class="block mt-1 w-full" />
                        <x-input-error for="smoke_exhaust_count" class="mt-2" />
                    </div>
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
