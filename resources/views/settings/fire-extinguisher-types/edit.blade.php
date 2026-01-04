<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edytuj Typ Gaśnicy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('settings.fire-extinguisher-types.update', $fireExtinguisherType) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6">
                            <!-- Name -->
                            <div class="col-span-1 sm:col-span-4">
                                <x-label for="name" value="{{ __('Nazwa typu') }}" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $fireExtinguisherType->name)" required autofocus />
                                <x-input-error for="name" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div class="col-span-1 sm:col-span-4">
                                <x-label for="description" value="{{ __('Opis (opcjonalnie)') }}" />
                                <textarea id="description" name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="3">{{ old('description', $fireExtinguisherType->description) }}</textarea>
                                <x-input-error for="description" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('settings.fire-extinguisher-types.index') }}" class="text-gray-600 hover:text-gray-900 underline mr-4">
                                {{ __('Anuluj') }}
                            </a>
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
