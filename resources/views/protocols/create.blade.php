<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nowy Protokół: ') . $system->name }} (Krok 1/4)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('protocols.store.step1', [$object, $system]) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Szablon -->
                            <div class="col-span-1">
                                <x-label for="template_id" value="{{ __('Szablon Protokołu') }}" />
                                <select id="template_id" name="template_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ $template->is_default ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="template_id" class="mt-2" />
                            </div>

                            <!-- Wykonawca -->
                            <div class="col-span-1">
                                <x-label for="performer_id" value="{{ __('Wykonawca Przeglądu') }}" />
                                <select id="performer_id" name="performer_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ Auth::id() == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="performer_id" class="mt-2" />
                            </div>

                            <!-- Data wykonania -->
                            <div class="col-span-1">
                                <x-label for="date" value="{{ __('Data Wykonania') }}" />
                                <x-input id="date" class="block mt-1 w-full" type="date" name="date" :value="date('Y-m-d')" required />
                                <x-input-error for="date" class="mt-2" />
                            </div>

                            <!-- Data następnego przeglądu -->
                            <div class="col-span-1">
                                <x-label for="next_date_option" value="{{ __('Następny przegląd za') }}" />
                                @if($system->has_periodic_review)
                                    <select id="next_date_option" name="next_date_option" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="12" selected>{{ __('12 miesięcy') }}</option>
                                        <option value="6">{{ __('6 miesięcy') }}</option>
                                        <option value="3">{{ __('3 miesiące') }}</option>
                                        <option value="none">{{ __('Nie dotyczy') }}</option>
                                    </select>
                                @else
                                    <x-input id="next_date_display" class="block mt-1 w-full bg-gray-100" type="text" value="{{ __('Nie dotyczy') }}" disabled />
                                    <input type="hidden" name="next_date_option" value="none" />
                                @endif
                                <x-input-error for="next_date_option" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-button class="ml-4">
                                {{ __('Dalej') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
