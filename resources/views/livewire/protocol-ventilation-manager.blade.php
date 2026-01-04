<div>
    <!-- Rozdzielnice -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">{{ __('Lista Rozdzielnic') }}</h3>
            <x-button wire:click="openDistributorModal">{{ __('+ Dodaj Rozdzielnicę') }}</x-button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oznaczenie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokalizacja</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($distributors as $distributor)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $distributor->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $distributor->location }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="moveDistributorUp({{ $distributor->id }})" class="text-gray-500 hover:text-gray-700" title="Przesuń w górę">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button wire:click="moveDistributorDown({{ $distributor->id }})" class="text-gray-500 hover:text-gray-700" title="Przesuń w dół">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <button wire:click="cloneDistributor({{ $distributor->id }})" class="text-green-600 hover:text-green-900 ml-2" title="Klonuj">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                    </button>
                                    <button wire:click="editDistributor({{ $distributor->id }})" class="text-indigo-600 hover:text-indigo-900 ml-2" title="Edytuj">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button wire:click="removeDistributor({{ $distributor->id }})" class="text-red-600 hover:text-red-900 ml-2" title="Usuń">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('Brak rozdzielnic w protokole.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Wentylatory -->
    <div class="mt-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">{{ __('Lista Wentylatorów') }}</h3>
            <x-button wire:click="openFanModal">{{ __('+ Dodaj Wentylator') }}</x-button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Typ/Numer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokalizacja</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($fans as $fan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $fan->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $fan->location }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="moveFanUp({{ $fan->id }})" class="text-gray-500 hover:text-gray-700" title="Przesuń w górę">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button wire:click="moveFanDown({{ $fan->id }})" class="text-gray-500 hover:text-gray-700" title="Przesuń w dół">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <button wire:click="cloneFan({{ $fan->id }})" class="text-green-600 hover:text-green-900 ml-2" title="Klonuj">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                    </button>
                                    <button wire:click="editFan({{ $fan->id }})" class="text-indigo-600 hover:text-indigo-900 ml-2" title="Edytuj">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button wire:click="removeFan({{ $fan->id }})" class="text-red-600 hover:text-red-900 ml-2" title="Usuń">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('Brak wentylatorów w protokole.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Rozdzielnicy -->
    <x-dialog-modal wire:model="showDistributorModal">
        <x-slot name="title">
            {{ $editingDistributorId ? __('Edytuj Rozdzielnicę') : __('Dodaj Rozdzielnicę') }}
        </x-slot>
        <x-slot name="content">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-label for="distName" value="{{ __('Oznaczenie/Numer') }}" />
                    <x-input wire:model="distName" id="distName" type="text" class="block mt-1 w-full" />
                    <x-input-error for="distName" class="mt-2" />
                </div>
                <div>
                    <x-label for="distLocation" value="{{ __('Lokalizacja') }}" />
                    <x-input wire:model="distLocation" id="distLocation" type="text" class="block mt-1 w-full" />
                    <x-input-error for="distLocation" class="mt-2" />
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDistributorModal', false)" wire:loading.attr="disabled">
                {{ __('Anuluj') }}
            </x-secondary-button>
            <x-button class="ml-2" wire:click="saveDistributor" wire:loading.attr="disabled">
                {{ $editingDistributorId ? __('Zapisz zmiany') : __('Dodaj') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Modal Wentylatora -->
    <x-dialog-modal wire:model="showFanModal">
        <x-slot name="title">
            {{ $editingFanId ? __('Edytuj Wentylator') : __('Dodaj Wentylator') }}
        </x-slot>
        <x-slot name="content">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-label for="fanName" value="{{ __('Typ/Numer') }}" />
                    <x-input wire:model="fanName" id="fanName" type="text" class="block mt-1 w-full" />
                    <x-input-error for="fanName" class="mt-2" />
                </div>
                <div>
                    <x-label for="fanLocation" value="{{ __('Lokalizacja') }}" />
                    <x-input wire:model="fanLocation" id="fanLocation" type="text" class="block mt-1 w-full" />
                    <x-input-error for="fanLocation" class="mt-2" />
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showFanModal', false)" wire:loading.attr="disabled">
                {{ __('Anuluj') }}
            </x-secondary-button>
            <x-button class="ml-2" wire:click="saveFan" wire:loading.attr="disabled">
                {{ $editingFanId ? __('Zapisz zmiany') : __('Dodaj') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
