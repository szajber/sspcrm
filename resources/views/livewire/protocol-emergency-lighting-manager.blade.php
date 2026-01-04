<div>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ __('Inwentaryzacja Oświetlenia Awaryjnego i Ewakuacyjnego') }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                {{ __('Dodaj lub edytuj urządzenia w obiekcie.') }}
            </p>
        </div>
        <div class="border-t border-gray-200 p-4">
            <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-end">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Typ lampy') }}</label>
                    <select id="type" wire:model="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">{{ __('Wybierz...') }}</option>
                        <option value="Awaryjna">{{ __('Awaryjna') }}</option>
                        <option value="Ewakuacyjna">{{ __('Ewakuacyjna') }}</option>
                        <option value="Wejście do budynku">{{ __('Wejście do budynku') }}</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">{{ __('Lokalizacja') }}</label>
                    <input type="text" id="location" wire:model="location" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $editId ? __('Zapisz zmiany') : __('Dodaj urządzenie') }}
                    </button>
                    @if($editId)
                        <button type="button" wire:click="cancelEdit" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Anuluj') }}
                        </button>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Lp.') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Typ') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Lokalizacja') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Akcje') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($devices as $index => $device)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $device->type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $device->location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $device->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('Edytuj') }}</button>
                                    <button wire:click="delete({{ $device->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('{{ __('Czy na pewno chcesz usunąć to urządzenie?') }}') || event.stopImmediatePropagation()">{{ __('Usuń') }}</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                    {{ __('Brak zdefiniowanych urządzeń dla tego obiektu.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
