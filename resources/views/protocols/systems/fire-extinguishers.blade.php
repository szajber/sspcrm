<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Typ') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lokalizacja') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Stan') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Rok nast. remontu') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Uwagi') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($extinguishers as $index => $item)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $index + 1 }}
                        <input type="hidden" name="extinguishers[{{ $index }}][id]" value="{{ $item->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $item->type_name }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->location }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <select name="extinguishers[{{ $index }}][status]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                            <option value="legalizacja" {{ $item->status === 'legalizacja' ? 'selected' : '' }}>{{ __('Legalizacja') }}</option>
                            <option value="remont" {{ $item->status === 'remont' ? 'selected' : '' }}>{{ __('Do remontu') }}</option>
                            <option value="zlom" {{ $item->status === 'zlom' ? 'selected' : '' }}>{{ __('Do złomowania') }}</option>
                            <option value="brak" {{ $item->status === 'brak' ? 'selected' : '' }}>{{ __('Brak gaśnicy') }}</option>
                            <option value="po_remoncie" {{ $item->status === 'po_remoncie' ? 'selected' : '' }}>{{ __('Po remoncie') }}</option>
                            <option value="nowa" {{ $item->status === 'nowa' ? 'selected' : '' }}>{{ __('Nowa') }}</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <input type="number" min="2000" max="2100" name="extinguishers[{{ $index }}][next_service_year]" value="{{ $item->next_service_year ?? date('Y') + 1 }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" placeholder="RRRR">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <input type="text" name="extinguishers[{{ $index }}][notes]" value="{{ $item->notes }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" placeholder="{{ __('Uwagi...') }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($extinguishers->isEmpty())
        <div class="text-center py-10 text-gray-500">
            {{ __('Brak zdefiniowanych gaśnic dla tego obiektu. Dodaj je najpierw w zakładce obiektu.') }}
        </div>
    @endif
</div>
