<div class="space-y-12">
    <!-- Centrale -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">{{ __('Centrale') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Nazwa</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-1/4">Wynik</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uwagi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($centrals as $index => $item)
                        <tr>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="font-bold">{{ $item->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->location }}</div>
                                <input type="hidden" name="centrals[{{ $index }}][id]" value="{{ $item->id }}">
                            </td>
                            <td class="px-3 py-4 text-center">
                                <select name="centrals[{{ $index }}][result]" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>Sprawna</option>
                                    <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>Niesprawna</option>
                                </select>
                            </td>
                            <td class="px-3 py-4">
                                <input type="text" name="centrals[{{ $index }}][notes]" value="{{ $item->notes }}" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detektory -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">{{ __('Detektory') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">Nazwa</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-1/6">Wynik</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-1/6">Następna Kalibracja</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uwagi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($detectors as $index => $item)
                        <tr>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="font-bold">{{ $item->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->location }}</div>
                                <input type="hidden" name="detectors[{{ $index }}][id]" value="{{ $item->id }}">
                            </td>
                            <td class="px-3 py-4 text-center">
                                <select name="detectors[{{ $index }}][result]" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>Sprawny</option>
                                    <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>Niesprawny</option>
                                    <option value="calibration" {{ $item->result === 'calibration' ? 'selected' : '' }}>Do kalibracji</option>
                                </select>
                            </td>
                            <td class="px-3 py-4 text-center">
                                <input type="month" name="detectors[{{ $index }}][next_calibration_date]" value="{{ $item->next_calibration_date ? $item->next_calibration_date->format('Y-m') : '' }}" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </td>
                            <td class="px-3 py-4">
                                <input type="text" name="detectors[{{ $index }}][notes]" value="{{ $item->notes }}" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Urządzenia Sterujące -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">{{ __('Urządzenia Sterujące') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Typ</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-1/4">Wynik</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uwagi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($controls as $index => $item)
                        <tr>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="font-bold">{{ $item->type }}</div>
                                <div class="text-xs text-gray-500">{{ $item->location }}</div>
                                <input type="hidden" name="controls[{{ $index }}][id]" value="{{ $item->id }}">
                            </td>
                            <td class="px-3 py-4 text-center">
                                <select name="controls[{{ $index }}][result]" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="positive" {{ $item->result === 'positive' ? 'selected' : '' }}>Sprawne</option>
                                    <option value="negative" {{ $item->result === 'negative' ? 'selected' : '' }}>Niesprawne</option>
                                </select>
                            </td>
                            <td class="px-3 py-4">
                                <input type="text" name="controls[{{ $index }}][notes]" value="{{ $item->notes }}" class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
