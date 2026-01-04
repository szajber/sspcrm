<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edycja Protokołu: ') . $protocol->number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('protocols.update', $protocol) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Szablon -->
                            <div class="col-span-1">
                                <x-label for="template_id" value="{{ __('Szablon Protokołu') }}" />
                                <select id="template_id" name="template_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ (isset($protocol->data['template_id']) && $protocol->data['template_id'] == $template->id) ? 'selected' : '' }}>
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
                                        <option value="{{ $user->id }}" {{ $protocol->performer_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error for="performer_id" class="mt-2" />
                            </div>

                            <!-- Data wykonania -->
                            <div class="col-span-1">
                                <x-label for="date" value="{{ __('Data Wykonania') }}" />
                                <x-input id="date" class="block mt-1 w-full" type="date" name="date" :value="$protocol->date->format('Y-m-d')" required />
                                <x-input-error for="date" class="mt-2" />
                            </div>

                            <!-- Data następnego przeglądu -->
                            <div class="col-span-1">
                                <x-label for="next_date_option" value="{{ __('Następny przegląd za') }}" />
                                @if($system->has_periodic_review)
                                    @php
                                        // Try to determine the option based on next_date
                                        $selectedOption = '12'; // Default
                                        if ($protocol->next_date) {
                                            $diff = $protocol->date->diffInMonths($protocol->next_date);
                                            // Approximate match
                                            if ($diff >= 11 && $diff <= 13) $selectedOption = '12';
                                            elseif ($diff >= 5 && $diff <= 7) $selectedOption = '6';
                                            elseif ($diff >= 2 && $diff <= 4) $selectedOption = '3';
                                        } else {
                                            $selectedOption = 'none';
                                        }
                                    @endphp
                                    <select id="next_date_option" name="next_date_option" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="12" {{ $selectedOption == '12' ? 'selected' : '' }}>{{ __('12 miesięcy') }}</option>
                                        <option value="6" {{ $selectedOption == '6' ? 'selected' : '' }}>{{ __('6 miesięcy') }}</option>
                                        <option value="3" {{ $selectedOption == '3' ? 'selected' : '' }}>{{ __('3 miesiące') }}</option>
                                        <option value="none" {{ $selectedOption == 'none' ? 'selected' : '' }}>{{ __('Nie dotyczy') }}</option>
                                    </select>
                                @else
                                    <x-input id="next_date_display" class="block mt-1 w-full bg-gray-100" type="text" value="{{ __('Nie dotyczy') }}" disabled />
                                    <input type="hidden" name="next_date_option" value="none" />
                                @endif
                                <x-input-error for="next_date_option" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('objects.show', $protocol->clientObject) }}" class="text-gray-600 hover:text-gray-900 underline">
                                {{ __('Anuluj') }}
                            </a>
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
