<div class="space-y-12">
    <!-- Sekcja Rozdzielnic -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">{{ __('Rozdzielnice Wentylacji') }}</h3>

        @foreach($protocolDistributors as $index => $distributor)
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Oznaczenie rozdzielnicy') }}</label>
                        <div class="mt-1 text-sm text-gray-900 font-bold">{{ $distributor->name }}</div>
                        <input type="hidden" name="distributors[{{ $index }}][id]" value="{{ $distributor->id }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Lokalizacja') }}</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $distributor->location }}</div>
                    </div>
                </div>

                <div class="space-y-4">
                    @php
                        $checks = [
                            'check_visual' => 'Ocena wizualna stanu technicznego',
                            'check_cables' => 'Ocena stanu przewodów i zacisków przyłączeniowych',
                            'check_devices' => 'Ocena stanu urządzeń wewnątrz szafy rozdzielczej',
                            'check_internal_cables' => 'Ocena stanu przewodów wewnątrz szafy',
                            'check_main_switch' => 'Sprawdzenie zadziałania wyłącznika głównego',
                            'check_manual_controls' => 'Sprawdzenie wysterowań ręcznych w rozdzielnicy',
                            'check_optical' => 'Sprawdzenie sygnalizacji optycznej rozdzielnicy',
                            'check_input_signals' => 'Sprawdzenie poprawności funkcjonowania na sygnały wejściowe',
                        ];
                    @endphp

                    @foreach($checks as $key => $label)
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="md:w-1/3">
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                </div>
                                <div class="md:w-1/4">
                                    <select name="distributors[{{ $index }}][{{ $key }}_status]" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="positive" {{ $distributor->{$key.'_status'} === 'positive' ? 'selected' : '' }}>Pozytywny</option>
                                        <option value="negative" {{ $distributor->{$key.'_status'} === 'negative' ? 'selected' : '' }}>Negatywny</option>
                                        <option value="not_applicable" {{ $distributor->{$key.'_status'} === 'not_applicable' ? 'selected' : '' }}>Nie dotyczy</option>
                                    </select>
                                </div>
                                <div class="md:w-1/3 flex-grow">
                                    <input type="text" name="distributors[{{ $index }}][{{ $key }}_notes]"
                                           value="{{ $distributor->{$key.'_notes'} }}"
                                           class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Uwagi...">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Dokumentacja (specjalny przypadek boolean) -->
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="md:w-1/3">
                                <span class="text-sm font-medium text-gray-700">{{ __('Sprawdzenie dokumentacji powykonawczej') }}</span>
                            </div>
                            <div class="md:w-1/4">
                                <select name="distributors[{{ $index }}][check_documentation_status]" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ $distributor->check_documentation_status ? 'selected' : '' }}>Jest</option>
                                    <option value="0" {{ !$distributor->check_documentation_status ? 'selected' : '' }}>Brak</option>
                                </select>
                            </div>
                            <div class="md:w-1/3 flex-grow">
                                <input type="text" name="distributors[{{ $index }}][check_documentation_notes]"
                                       value="{{ $distributor->check_documentation_notes }}"
                                       class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       placeholder="Uwagi...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Sekcja Wentylatorów -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">{{ __('Wentylatory') }}</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Lp.</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Urządzenie</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Alarm II st.</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Stan Tech.</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Przewody</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Prąd I bieg</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Prąd II bieg</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Ocena</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uwagi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($protocolFans as $index => $fan)
                        <tr>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                {{ $index + 1 }}
                                <input type="hidden" name="fans[{{ $index }}][id]" value="{{ $fan->id }}">
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="font-bold">{{ $fan->name }}</div>
                                <div class="text-xs text-gray-500">{{ $fan->location }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                <input type="checkbox" name="fans[{{ $index }}][check_alarm_level_2]" value="1"
                                       {{ $fan->check_alarm_level_2 ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <select name="fans[{{ $index }}][check_technical_condition]" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="good" {{ $fan->check_technical_condition === 'good' ? 'selected' : '' }}>Poprawny</option>
                                    <option value="bad" {{ $fan->check_technical_condition === 'bad' ? 'selected' : '' }}>Uszkodzony</option>
                                </select>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <select name="fans[{{ $index }}][check_cables_condition]" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="good" {{ $fan->check_cables_condition === 'good' ? 'selected' : '' }}>Poprawny</option>
                                    <option value="bad" {{ $fan->check_cables_condition === 'bad' ? 'selected' : '' }}>Uszkodzony</option>
                                </select>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <input type="text" name="fans[{{ $index }}][current_1]" value="{{ $fan->current_1 }}"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Amper/Norma">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <input type="text" name="fans[{{ $index }}][current_2]" value="{{ $fan->current_2 }}"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Amper/Norma">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <select name="fans[{{ $index }}][result]" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="positive" {{ $fan->result === 'positive' ? 'selected' : '' }}>Sprawne</option>
                                    <option value="negative" {{ $fan->result === 'negative' ? 'selected' : '' }}>Niesprawne</option>
                                </select>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <input type="text" name="fans[{{ $index }}][notes]" value="{{ $fan->notes }}"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
