<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ustawienia Firmy') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">{{ __('Dane Firmy') }}</x-slot>
                    <x-slot name="description">{{ __('Zaktualizuj dane firmy widoczne w systemie.') }}</x-slot>
                </x-section-title>

                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Logo -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="logo" value="{{ __('Logo') }}" />

                                    @if ($company->logo_path)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($company->logo_path) }}" alt="{{ $company->name }}" class="h-20 object-contain">
                                        </div>
                                    @endif

                                    <input type="file" id="logo" name="logo" class="mt-1 block w-full" />
                                    <x-input-error for="logo" class="mt-2" />
                                </div>

                                <!-- Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="name" value="{{ __('Nazwa Firmy') }}" />
                                    <x-input id="name" type="text" class="mt-1 block w-full" name="name" value="{{ old('name', $company->name) }}" required autofocus />
                                    <x-input-error for="name" class="mt-2" />
                                </div>

                                <!-- Address -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="address" value="{{ __('Adres') }}" />
                                    <x-input id="address" type="text" class="mt-1 block w-full" name="address" value="{{ old('address', $company->address) }}" />
                                    <x-input-error for="address" class="mt-2" />
                                </div>

                                <!-- Postal Code -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="postal_code" value="{{ __('Kod Pocztowy') }}" />
                                    <x-input id="postal_code" type="text" class="mt-1 block w-full" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" />
                                    <x-input-error for="postal_code" class="mt-2" />
                                </div>

                                <!-- City -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="city" value="{{ __('Miasto') }}" />
                                    <x-input id="city" type="text" class="mt-1 block w-full" name="city" value="{{ old('city', $company->city) }}" />
                                    <x-input-error for="city" class="mt-2" />
                                </div>

                                <!-- Phone -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="phone" value="{{ __('Telefon') }}" />
                                    <x-input id="phone" type="text" class="mt-1 block w-full" name="phone" value="{{ old('phone', $company->phone) }}" />
                                    <x-input-error for="phone" class="mt-2" />
                                </div>

                                <!-- Email -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="email" value="{{ __('Email') }}" />
                                    <x-input id="email" type="email" class="mt-1 block w-full" name="email" value="{{ old('email', $company->email) }}" />
                                    <x-input-error for="email" class="mt-2" />
                                </div>

                                <!-- Website -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="website" value="{{ __('Strona WWW') }}" />
                                    <x-input id="website" type="text" class="mt-1 block w-full" name="website" value="{{ old('website', $company->website) }}" />
                                    <x-input-error for="website" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                            @if (session('status') === 'settings-updated')
                                <div class="mr-3 text-sm text-gray-600">
                                    {{ __('Zapisano.') }}
                                </div>
                            @endif

                            <x-button>
                                {{ __('Zapisz') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
