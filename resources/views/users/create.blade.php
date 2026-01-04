<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nowy Użytkownik') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">{{ __('Dane Użytkownika') }}</x-slot>
                    <x-slot name="description">{{ __('Wprowadź dane, aby utworzyć nowe konto użytkownika.') }}</x-slot>
                </x-section-title>

                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="name" value="{{ __('Nazwa') }}" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                                    <x-input-error for="name" class="mt-2" />
                                </div>

                                <!-- Email -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="email" value="{{ __('Email') }}" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                                    <x-input-error for="email" class="mt-2" />
                                </div>

                                <!-- Role -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="role" value="{{ __('Rola') }}" />
                                    <select id="role" name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        @foreach (\App\Enums\UserRole::cases() as $role)
                                            <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="role" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="password" value="{{ __('Hasło') }}" />
                                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                    <x-input-error for="password" class="mt-2" />
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="password_confirmation" value="{{ __('Potwierdź Hasło') }}" />
                                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                                    <x-input-error for="password_confirmation" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                            <x-button>
                                {{ __('Utwórz') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
