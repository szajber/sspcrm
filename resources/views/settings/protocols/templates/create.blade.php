<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dodaj Szablon: ') . $system->name }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">{{ __('Dane Szablonu') }}</x-slot>
                    <x-slot name="description">{{ __('Zdefiniuj nowy zestaw ustawień dla protokołu.') }}</x-slot>
                </x-section-title>

                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('settings.protocols.templates.store', $system) }}">
                        @csrf

                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                            <div class="grid grid-cols-6 gap-6">
                                <!-- Template Name -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="name" value="{{ __('Nazwa Szablonu') }}" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="np. Standardowy" />
                                    <x-input-error for="name" class="mt-2" />
                                </div>

                                <!-- Protocol Title -->
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="title" value="{{ __('Tytuł Protokołu') }}" />
                                    <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" placeholder="np. Protokół przeglądu gaśnic" />
                                    <x-input-error for="title" class="mt-2" />
                                </div>

                                <!-- Description (Editor) -->
                                <div class="col-span-6">
                                    <x-label for="description" value="{{ __('Dodatkowe informacje (Opis)') }}" />
                                    <textarea id="description" name="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="10">{{ old('description') }}</textarea>
                                    <x-input-error for="description" class="mt-2" />
                                </div>

                                <!-- Default -->
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="is_default" class="flex items-center">
                                        <x-checkbox id="is_default" name="is_default" value="1" />
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Ustaw jako domyślny') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                            <x-button>
                                {{ __('Zapisz') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.tiny.cloud/1/5g65y1brfdq1cc4nfo3umh4kqplpln2fa7kozrqnmaczzbq8/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#description',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
            toolbar_mode: 'floating',
            height: 400
        });
    </script>
</x-app-layout>
