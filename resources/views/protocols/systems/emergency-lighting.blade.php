<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Typ lampy') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lokalizacja') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Uruchomienie < 2s') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Świecenie > 1h') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Wynik') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Uwagi') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($protocolLighting as $index => $item)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $index + 1 }}
                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $item->type }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->location }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                        <input type="checkbox" name="items[{{ $index }}][check_startup_time]" value="1" {{ $item->check_startup_time ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                        <input type="checkbox" name="items[{{ $index }}][check_duration]" value="1" {{ $item->check_duration ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
