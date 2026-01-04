<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edytuj Obiekt') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">{{ __('Dane Obiektu') }}</x-slot>
                    <x-slot name="description">{{ __('Zaktualizuj dane obiektu.') }}</x-slot>
                </x-section-title>

                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('objects.update', $object) }}">
                        @csrf
                        @method('PUT')

                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="name" value="{{ __('Nazwa Obiektu') }}" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $object->name)" required autofocus />
                                    <x-input-error for="name" class="mt-2" />
                                </div>

                                <!-- Client -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="client_id" value="{{ __('Klient') }}" />
                                    <select id="client_id" name="client_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="">{{ __('Brak (Obiekt bez klienta)') }}</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id', $object->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="client_id" class="mt-2" />
                                </div>

                                <!-- Address -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="address" value="{{ __('Adres') }}" />
                                    <x-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address', $object->address)" />
                                    <x-input-error for="address" class="mt-2" />
                                </div>

                                <!-- Postal Code -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="postal_code" value="{{ __('Kod Pocztowy') }}" />
                                    <x-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $object->postal_code)" />
                                    <x-input-error for="postal_code" class="mt-2" />
                                </div>

                                <!-- City -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="city" value="{{ __('Miasto') }}" />
                                    <x-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $object->city)" />
                                    <x-input-error for="city" class="mt-2" />
                                </div>

                                <!-- Notes -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="notes" value="{{ __('Uwagi') }}" />
                                    <textarea id="notes" name="notes" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="3">{{ old('notes', $object->notes) }}</textarea>
                                    <x-input-error for="notes" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="is_active" class="flex items-center">
                                        <x-checkbox id="is_active" name="is_active" value="1" :checked="$object->is_active" />
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Aktywny') }}</span>
                                    </label>
                                    <x-input-error for="is_active" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                            <x-button>
                                {{ __('Zaktualizuj') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
