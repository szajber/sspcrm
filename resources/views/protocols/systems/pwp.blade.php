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
            @foreach($protocolPwpDevices as $index => $item)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $index + 1 }}
                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        PWP {{ $item->system_number }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        {{ $item->type === 'switch' ? __('Wyłącznik') : __('Punkt aktywacji') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->location }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if($item->type === 'switch')
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="items[{{ $index }}][check_activation]" value="1" {{ $item->check_activation ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="text-xs">{{ __('Zadziałanie') }}</span>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_access]" value="1" {{ $item->check_access ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Dostęp') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_signage]" value="1" {{ $item->check_signage ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Oznakowanie') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_condition]" value="1" {{ $item->check_condition ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Stan tech.') }}</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="items[{{ $index }}][check_activation]" value="1" {{ $item->check_activation ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-xs">{{ __('Zadziałanie') }}</span>
                                </label>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <select name="items[{{ $index }}][result]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                            <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>{{ __('Pozytywny') }}</option>
                            <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>{{ __('Negatywny') }}</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <input type="text" name="items[{{ $index }}][notes]" value="{{ $item->notes }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" placeholder="{{ __('Uwagi...') }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
