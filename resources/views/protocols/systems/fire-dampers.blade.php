<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Typ') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lokalizacja') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('Optyczna kontrola urządzeń') }}">{{ __('Opt.') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('Sprawdzenie napędu mechanicznego') }}">{{ __('Napęd') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('Sprawdzenie części mechanicznych') }}">{{ __('Mech.') }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('Sprawdzenie zadziałania w trybie alarmowym') }}">{{ __('Alarm') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Wynik') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Uwagi') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($protocolDampers as $index => $item)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $index + 1 }}
                        <input type="hidden" name="dampers[{{ $index }}][id]" value="{{ $item->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $item->type_name }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->location }}
                    </td>

                    <!-- Checkboxy -->
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" name="dampers[{{ $index }}][check_optical]" value="1" {{ $item->check_optical ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" name="dampers[{{ $index }}][check_drive]" value="1" {{ $item->check_drive ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" name="dampers[{{ $index }}][check_mechanical]" value="1" {{ $item->check_mechanical ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" name="dampers[{{ $index }}][check_alarm]" value="1" {{ $item->check_alarm ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <select name="dampers[{{ $index }}][result]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                            <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>{{ __('Pozytywny') }}</option>
                            <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>{{ __('Negatywny') }}</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <input type="text" name="dampers[{{ $index }}][notes]" value="{{ $item->notes }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" placeholder="{{ __('Uwagi...') }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
