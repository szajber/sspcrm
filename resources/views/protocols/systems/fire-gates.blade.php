<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('System') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Urządzenie') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lokalizacja') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sprawdzenia') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Wynik') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Uwagi') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($protocolFireGateDevices as $index => $item)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $index + 1 }}
                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        Sys {{ $item->system_number }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        @if($item->type === 'gate')
                            <div><strong>{{ __('Brama') }}</strong></div>
                            <div class="text-xs text-gray-500">{{ $item->gate_type === 'electric' ? 'Elektryczna' : 'Grawitacyjna' }}</div>
                            <div class="text-xs text-gray-500">EI: {{ $item->fire_resistance_class }}</div>
                        @else
                            <div><strong>{{ __('Centrala') }}</strong></div>
                            <div class="text-xs text-gray-500">{{ $item->manufacturer }} {{ $item->model }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->location }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if($item->type === 'gate')
                            <div class="flex items-center space-x-2">
                                <span class="text-xs">{{ __('Zadziałanie:') }}</span>
                                <select name="items[{{ $index }}][result]" class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>{{ __('Sprawny') }}</option>
                                    <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>{{ __('Niesprawny') }}</option>
                                </select>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_detectors]" value="1" {{ $item->check_detectors ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Czujki') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_buttons]" value="1" {{ $item->check_buttons ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Przyciski') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_signalers]" value="1" {{ $item->check_signalers ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Sygnalizatory') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_holding_mechanism]" value="1" {{ $item->check_holding_mechanism ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Trzymacz') }}</span>
                                </label>
                                <label class="flex items-center space-x-2 col-span-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_drive]" value="1" {{ $item->check_drive ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Silnik napędowy') }}</span>
                                </label>
                                <div class="col-span-2 mt-1">
                                    <span class="text-xs block">{{ __('Data akumulatorów:') }}</span>
                                    <input type="text" name="items[{{ $index }}][battery_date]" value="{{ $item->battery_date }}" class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" placeholder="RRRR">
                                </div>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        @if($item->type === 'central')
                            <select name="items[{{ $index }}][result]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                                <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>{{ __('Sprawny') }}</option>
                                <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>{{ __('Niesprawny') }}</option>
                            </select>
                        @else
                           <!-- Wynik bramy jest wybierany wyżej w kolumnie sprawdzenia, ale dla spójności może być tutaj -->
                           <!-- Wcześniej dałem select w kolumnie sprawdzenia dla bramy. Przenieśmy go tutaj. -->
                            <select name="items[{{ $index }}][result]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                                <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>{{ __('Sprawny') }}</option>
                                <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>{{ __('Niesprawny') }}</option>
                            </select>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <input type="text" name="items[{{ $index }}][notes]" value="{{ $item->notes }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" placeholder="{{ __('Uwagi...') }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
