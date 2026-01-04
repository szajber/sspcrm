<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edytuj Klienta') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">{{ __('Dane Klienta') }}</x-slot>
                    <x-slot name="description">{{ __('Zaktualizuj dane klienta.') }}</x-slot>
                </x-section-title>

                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('clients.update', $client) }}">
                        @csrf
                        @method('PUT')

                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="name" value="{{ __('Nazwa') }}" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $client->name)" required autofocus />
                                    <x-input-error for="name" class="mt-2" />
                                </div>

                                <!-- NIP -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="nip" value="{{ __('NIP') }}" />
                                    <x-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip', $client->nip)" />
                                    <x-input-error for="nip" class="mt-2" />
                                </div>

                                <!-- Address -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="address" value="{{ __('Adres') }}" />
                                    <x-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address', $client->address)" />
                                    <x-input-error for="address" class="mt-2" />
                                </div>

                                <!-- Postal Code -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="postal_code" value="{{ __('Kod Pocztowy') }}" />
                                    <x-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $client->postal_code)" />
                                    <x-input-error for="postal_code" class="mt-2" />
                                </div>

                                <!-- City -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="city" value="{{ __('Miasto') }}" />
                                    <x-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $client->city)" />
                                    <x-input-error for="city" class="mt-2" />
                                </div>

                                <!-- Phone -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="phone" value="{{ __('Telefon') }}" />
                                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $client->phone)" />
                                    <x-input-error for="phone" class="mt-2" />
                                </div>

                                <!-- Email -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="email" value="{{ __('Email') }}" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $client->email)" />
                                    <x-input-error for="email" class="mt-2" />
                                </div>

                                <!-- Notes -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="notes" value="{{ __('Uwagi') }}" />
                                    <textarea id="notes" name="notes" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="3">{{ old('notes', $client->notes) }}</textarea>
                                    <x-input-error for="notes" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="is_active" class="flex items-center">
                                        <x-checkbox id="is_active" name="is_active" value="1" :checked="$client->is_active" />
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
