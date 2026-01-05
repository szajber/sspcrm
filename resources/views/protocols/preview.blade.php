<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kreator Protokołu - Krok 4/4: Podgląd') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Progress Bar -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Dane podstawowe</span>
                            <span>Lista urządzeń</span>
                            <span>Badanie stanu</span>
                            <span class="font-bold text-indigo-600">Podgląd i PDF</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="border p-8 bg-gray-50 mb-6">
                        <div class="text-center mb-8">
                            <h1 class="text-2xl font-bold uppercase">{{ $template->title ?? 'PROTOKÓŁ PRZEGLĄDU' }}</h1>
                            <p class="text-lg font-mono mt-2">{{ $protocol->number }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div>
                                <h3 class="font-bold text-sm uppercase text-gray-500 mb-2">{{ __('ZAMAWIAJĄCY') }}</h3>
                                @if($protocol->clientObject->client)
                                    <p class="font-bold">{{ $protocol->clientObject->client->name }}</p>
                                    <p>{{ $protocol->clientObject->client->address }}</p>
                                    <p>{{ $protocol->clientObject->client->postal_code }} {{ $protocol->clientObject->client->city }}</p>
                                @else
                                    <p class="font-bold">{{ __('Brak przypisanego klienta') }}</p>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-sm uppercase text-gray-500 mb-2">{{ __('OBIEKT') }}</h3>
                                <p class="font-bold">{{ $protocol->clientObject->name }}</p>
                                <p>{{ $protocol->clientObject->address }}</p>
                                <p>{{ $protocol->clientObject->postal_code }} {{ $protocol->clientObject->city }}</p>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="font-bold text-sm uppercase text-gray-500 mb-2">{{ __('INFORMACJE OGÓLNE') }}</h3>
                            <div class="prose max-w-none text-sm">
                                {!! $template->description !!}
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="font-bold text-sm uppercase text-gray-500 mb-2">{{ __('TERMINY') }}</h3>
                            <p>{{ __('Data wykonania przeglądu: ') }} <strong>{{ $protocol->date->format('d.m.Y') }}</strong></p>
                            @if($protocol->next_date)
                                <p>{{ __('Data następnego przeglądu: ') }} <strong>{{ $protocol->next_date->format('m.Y') }}</strong></p>
                            @else
                                <p>{{ __('Data następnego przeglądu: ') }} <strong>{{ __('Nie dotyczy') }}</strong></p>
                            @endif
                        </div>

                        <div class="mb-8">
                            <h3 class="font-bold text-sm uppercase text-gray-500 mb-2">{{ __('UWAGI KOŃCOWE') }}</h3>
                            <div class="prose max-w-none text-sm">
                                {!! $protocol->data['final_notes'] ?? __('Brak') !!}
                            </div>
                        </div>

                        @if($protocol->system->slug === 'drzwi-przeciwpozarowe')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Klasa odporności</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Stan</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($doors as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $item->resistance_class }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->status === 'sprawne' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $statuses[$item->status] ?? $item->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie Ilościowe') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm border">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-700 border-r">Klasa odporności</th>
                                                <th class="px-3 py-2 text-center font-bold text-gray-900 border-r">Ilość (Suma)</th>
                                                @foreach($statuses as $key => $label)
                                                    <th class="px-3 py-2 text-center font-medium text-gray-500 border-r">{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($stats as $type => $data)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 font-medium text-gray-900 border-r">{{ $type }}</td>
                                                    <td class="px-3 py-2 text-center font-bold text-indigo-600 border-r">{{ $data['total'] }}</td>
                                                    @foreach($statuses as $key => $label)
                                                        <td class="px-3 py-2 text-center text-gray-500 border-r">
                                                            {{ $data[$key] > 0 ? $data[$key] : '-' }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="bg-gray-50 font-bold border-t-2 border-gray-300">
                                                <td class="px-3 py-2 text-gray-900 border-r">SUMA</td>
                                                <td class="px-3 py-2 text-center text-indigo-800 border-r">{{ $totals['total'] }}</td>
                                                @foreach($statuses as $key => $label)
                                                    <td class="px-3 py-2 text-center text-gray-700 border-r">
                                                        {{ $totals[$key] > 0 ? $totals[$key] : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Sekcja Klap (jeśli system to klapy) -->
                        @if($protocol->system->slug === 'klapy-pozarowe')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Typ</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500" title="Optyczna kontrola">Opt.</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500" title="Napęd mechaniczny">Napęd</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500" title="Części mechaniczne">Mech.</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500" title="Tryb alarmowy">Alarm</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($dampers as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $item->type_name }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2 text-center">{{ $item->check_optical ? 'Tak' : 'Nie' }}</td>
                                                    <td class="px-3 py-2 text-center">{{ $item->check_drive ? 'Tak' : 'Nie' }}</td>
                                                    <td class="px-3 py-2 text-center">{{ $item->check_mechanical ? 'Tak' : 'Nie' }}</td>
                                                    <td class="px-3 py-2 text-center">{{ $item->check_alarm ? 'Tak' : 'Nie' }}</td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Pozytywny' : 'Negatywny' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie') }}</h3>
                                <div class="bg-white rounded shadow p-4 grid grid-cols-3 gap-4 text-center">
                                    <div class="p-4 bg-gray-50 rounded">
                                        <div class="text-sm text-gray-500">{{ __('Liczba klap') }}</div>
                                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                                    </div>
                                    <div class="p-4 bg-green-50 rounded">
                                        <div class="text-sm text-green-600">{{ __('Wynik Pozytywny') }}</div>
                                        <div class="text-2xl font-bold text-green-700">{{ $stats['positive'] }}</div>
                                    </div>
                                    <div class="p-4 bg-red-50 rounded">
                                        <div class="text-sm text-red-600">{{ __('Wynik Negatywny') }}</div>
                                        <div class="text-2xl font-bold text-red-700">{{ $stats['negative'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Sekcja Oddymiania (jeśli system to oddymianie) -->
                        @if($protocol->system->slug === 'system-oddymiania')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Centrala</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Rok prod. akum.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($smokeSystems as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $item->central_type_name }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->battery_date }}</td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Pozytywny' : 'Negatywny' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie') }}</h3>
                                <div class="bg-white rounded shadow p-4 grid grid-cols-3 gap-4 text-center">
                                    <div class="p-4 bg-gray-50 rounded">
                                        <div class="text-sm text-gray-500">{{ __('Liczba systemów') }}</div>
                                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                                    </div>
                                    <div class="p-4 bg-green-50 rounded">
                                        <div class="text-sm text-green-600">{{ __('Wynik Pozytywny') }}</div>
                                        <div class="text-2xl font-bold text-green-700">{{ $stats['positive'] }}</div>
                                    </div>
                                    <div class="p-4 bg-red-50 rounded">
                                        <div class="text-sm text-red-600">{{ __('Wynik Negatywny') }}</div>
                                        <div class="text-2xl font-bold text-red-700">{{ $stats['negative'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Sekcja Oświetlenia (jeśli system to oświetlenie) -->
                        @if($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Typ</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Uruch. &lt; 2s</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Czas &gt; 1h</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($lightingDevices as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $item->type }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        {{ $item->check_startup_time ? 'Tak' : 'Nie' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        {{ $item->check_duration ? 'Tak' : 'Nie' }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Pozytywny' : 'Negatywny' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm border">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-700 border-r">Typ lampy</th>
                                                <th class="px-3 py-2 text-center font-bold text-gray-900 border-r">Ilość (Suma)</th>
                                                <th class="px-3 py-2 text-center font-medium text-green-700 border-r">Pozytywne</th>
                                                <th class="px-3 py-2 text-center font-medium text-red-700 border-r">Negatywne</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($stats as $type => $data)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 font-medium text-gray-900 border-r">{{ $type }}</td>
                                                    <td class="px-3 py-2 text-center font-bold text-indigo-600 border-r">{{ $data['total'] }}</td>
                                                    <td class="px-3 py-2 text-center text-green-600 border-r">{{ $data['positive'] }}</td>
                                                    <td class="px-3 py-2 text-center text-red-600 border-r">{{ $data['negative'] }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-gray-50 font-bold border-t-2 border-gray-300">
                                                <td class="px-3 py-2 text-gray-900 border-r">SUMA</td>
                                                <td class="px-3 py-2 text-center text-indigo-800 border-r">{{ $totals['total'] }}</td>
                                                <td class="px-3 py-2 text-center text-green-800 border-r">{{ $totals['positive'] }}</td>
                                                <td class="px-3 py-2 text-center text-red-800 border-r">{{ $totals['negative'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Sekcja PWP (jeśli system to PWP) -->
                        @if($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">System</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Urządzenie</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Elementy</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($pwpDevices as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">PWP {{ $item->system_number }}</td>
                                                    <td class="px-3 py-2 text-gray-700">{{ $item->type === 'switch' ? 'Wyłącznik' : 'Punkt aktywacji' }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2 text-xs text-gray-500">
                                                        @if($item->type === 'switch')
                                                            <div>Zadziałanie: {{ $item->check_activation ? 'Tak' : 'Nie' }}</div>
                                                        @else
                                                            <div>Dostęp: {{ $item->check_access ? 'Tak' : 'Nie' }}</div>
                                                            <div>Oznakowanie: {{ $item->check_signage ? 'Tak' : 'Nie' }}</div>
                                                            <div>Stan tech.: {{ $item->check_condition ? 'Tak' : 'Nie' }}</div>
                                                            <div>Zadziałanie: {{ $item->check_activation ? 'Tak' : 'Nie' }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Pozytywny' : 'Negatywny' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie') }}</h3>
                                <div class="bg-white rounded shadow p-4 grid grid-cols-3 gap-4 text-center">
                                    <div class="p-4 bg-gray-50 rounded">
                                        <div class="text-sm text-gray-500">{{ __('Liczba systemów') }}</div>
                                        <div class="text-2xl font-bold text-gray-900">{{ $totals['total'] }}</div>
                                    </div>
                                    <div class="p-4 bg-green-50 rounded">
                                        <div class="text-sm text-green-600">{{ __('Systemy sprawne') }}</div>
                                        <div class="text-2xl font-bold text-green-700">{{ $totals['positive'] }}</div>
                                    </div>
                                    <div class="p-4 bg-red-50 rounded">
                                        <div class="text-sm text-red-600">{{ __('Systemy niesprawne') }}</div>
                                        <div class="text-2xl font-bold text-red-700">{{ $totals['negative'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Sekcja Bram (jeśli system to Bramy) -->
                        @if($protocol->system->slug === 'bramy-i-grodzie-przeciwpozarowe')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">System</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Urządzenie</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Elementy</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($fireGateDevices as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">Sys {{ $item->system_number }}</td>
                                                    <td class="px-3 py-2 text-gray-700">
                                                        @if($item->type === 'gate')
                                                            <div><strong>Brama</strong></div>
                                                            <div class="text-xs text-gray-500">{{ $item->gate_type === 'electric' ? 'Elektryczna' : 'Grawitacyjna' }}</div>
                                                            @if($item->fire_resistance_class)<div class="text-xs text-gray-500">EI: {{ $item->fire_resistance_class }}</div>@endif
                                                        @else
                                                            <div><strong>Centrala</strong></div>
                                                            <div class="text-xs text-gray-500">{{ $item->manufacturer }} {{ $item->model }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2 text-xs text-gray-500">
                                                        @if($item->type === 'gate')
                                                            <div>Przeciwwaga: {{ $item->check_counterweight ? 'Tak' : 'Nie' }}</div>
                                                            <div>Sprzęgło magn.: {{ $item->check_magnetic_clutch ? 'Tak' : 'Nie' }}</div>
                                                            <div>Trzymacz magn.: {{ $item->check_holding_mechanism ? 'Tak' : 'Nie' }}</div>
                                                            <div>Silnik: {{ $item->check_drive ? 'Tak' : 'Nie' }}</div>
                                                        @else
                                                            <div>Czujki: {{ $item->check_detectors ? 'Tak' : 'Nie' }}</div>
                                                            <div>Przyciski: {{ $item->check_buttons ? 'Tak' : 'Nie' }}</div>
                                                            <div>Przycisk test: {{ $item->check_test_button ? 'Tak' : 'Nie' }}</div>
                                                            <div>Sygnalizatory: {{ $item->check_signalers ? 'Tak' : 'Nie' }}</div>
                                                            @if($item->battery_date)<div>Akumulatory: {{ $item->battery_date }}</div>@endif
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Sprawny' : 'Niesprawny' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie') }}</h3>
                                <div class="bg-white rounded shadow p-4 grid grid-cols-2 gap-8">
                                    <!-- Podsumowanie Bram -->
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-2 border-b pb-1">Bramy ({{ $totals['gates'] }})</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="p-3 bg-green-50 rounded text-center">
                                                <div class="text-xs text-green-600">Sprawne</div>
                                                <div class="text-xl font-bold text-green-700">{{ $totals['gates_positive'] }}</div>
                                            </div>
                                            <div class="p-3 bg-red-50 rounded text-center">
                                                <div class="text-xs text-red-600">Niesprawne</div>
                                                <div class="text-xl font-bold text-red-700">{{ $totals['gates_negative'] }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Podsumowanie Central -->
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-2 border-b pb-1">Centrale ({{ $totals['centrals'] }})</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="p-3 bg-green-50 rounded text-center">
                                                <div class="text-xs text-green-600">Sprawne</div>
                                                <div class="text-xl font-bold text-green-700">{{ $totals['centrals_positive'] }}</div>
                                            </div>
                                            <div class="p-3 bg-red-50 rounded text-center">
                                                <div class="text-xs text-red-600">Niesprawne</div>
                                                <div class="text-xl font-bold text-red-700">{{ $totals['centrals_negative'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Sekcja Gaśnic (jeśli system to gaśnice) -->
                        @if($protocol->system->slug === 'gasnice')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Raport Szczegółowy') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lp.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Typ</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Lokalizacja</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Stan</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Rok nast. rem.</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($extinguishers as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $item->type_name }}</td>
                                                    <td class="px-3 py-2 text-gray-500">{{ $item->location }}</td>
                                                    <td class="px-3 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->status === 'legalizacja' ? 'bg-green-100 text-green-800' :
                                                               ($item->status === 'remont' ? 'bg-yellow-100 text-yellow-800' :
                                                               ($item->status === 'zlom' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                            {{ $statuses[$item->status] ?? $item->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-500">
                                                        {{ $item->next_service_year ?? '-' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 page-break-inside-avoid">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Podsumowanie Ilościowe') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm border">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-700 border-r">Typ gaśnicy</th>
                                                <th class="px-3 py-2 text-center font-bold text-gray-900 border-r">Ilość (Suma)</th>
                                                @foreach($statuses as $key => $label)
                                                    <th class="px-3 py-2 text-center font-medium text-gray-500 border-r">{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($stats as $type => $data)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 font-medium text-gray-900 border-r">{{ $type }}</td>
                                                    <td class="px-3 py-2 text-center font-bold text-indigo-600 border-r">{{ $data['total'] }}</td>
                                                    @foreach($statuses as $key => $label)
                                                        <td class="px-3 py-2 text-center text-gray-500 border-r">
                                                            {{ $data[$key] > 0 ? $data[$key] : '-' }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="bg-gray-50 font-bold border-t-2 border-gray-300">
                                                <td class="px-3 py-2 text-gray-900 border-r">SUMA</td>
                                                <td class="px-3 py-2 text-center text-indigo-800 border-r">{{ $totals['total'] }}</td>
                                                @foreach($statuses as $key => $label)
                                                    <td class="px-3 py-2 text-center text-gray-700 border-r">
                                                        {{ $totals[$key] > 0 ? $totals[$key] : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if($protocol->system->slug === 'wentylacja')
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Rozdzielnice Wentylacji') }}</h3>
                                @php
                                    $statusMap = [
                                        'positive' => 'Pozytywny',
                                        'negative' => 'Negatywny',
                                        'not_applicable' => 'Nie dotyczy',
                                    ];
                                    $checks = [
                                        'check_visual' => 'Ocena wizualna',
                                        'check_cables' => 'Przewody i zaciski',
                                        'check_devices' => 'Urządzenia wewn.',
                                        'check_internal_cables' => 'Przewody wewn.',
                                        'check_main_switch' => 'Wyłącznik główny',
                                        'check_manual_controls' => 'Wysterowania ręczne',
                                        'check_optical' => 'Sygnalizacja optyczna',
                                        'check_input_signals' => 'Sygnały wejściowe',
                                    ];
                                @endphp

                                @foreach($distributors as $distributor)
                                    <div class="mb-6 border rounded-lg p-4 bg-gray-50">
                                        <div class="font-bold text-lg mb-2">{{ $distributor->name }} <span class="text-sm font-normal text-gray-500">({{ $distributor->location }})</span></div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($checks as $key => $label)
                                                <div class="text-sm border-b border-gray-200 pb-1">
                                                    <span class="font-semibold">{{ $label }}:</span>
                                                    <span class="{{ $distributor->{$key.'_status'} === 'negative' ? 'text-red-600 font-bold' : '' }}">
                                                        {{ $statusMap[$distributor->{$key.'_status'}] }}
                                                    </span>
                                                    @if($distributor->{$key.'_notes'})
                                                        <div class="text-xs text-gray-500 mt-1 italic">Uwagi: {{ $distributor->{$key.'_notes'} }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                            <div class="text-sm border-b border-gray-200 pb-1">
                                                <span class="font-semibold">Dokumentacja:</span>
                                                <span>{{ $distributor->check_documentation_status ? 'Jest' : 'Brak' }}</span>
                                                @if($distributor->check_documentation_notes)
                                                    <div class="text-xs text-gray-500 mt-1 italic">Uwagi: {{ $distributor->check_documentation_notes }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Wentylatory') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-2 py-2 text-left">Lp.</th>
                                                <th class="px-2 py-2 text-left">Urządzenie</th>
                                                <th class="px-2 py-2 text-center">Alarm II</th>
                                                <th class="px-2 py-2 text-center">Stan Tech.</th>
                                                <th class="px-2 py-2 text-center">Przewody</th>
                                                <th class="px-2 py-2 text-center">Prąd I</th>
                                                <th class="px-2 py-2 text-center">Prąd II</th>
                                                <th class="px-2 py-2 text-center">Wynik</th>
                                                <th class="px-2 py-2 text-left">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach($fans as $index => $fan)
                                                <tr>
                                                    <td class="px-2 py-2">{{ $index + 1 }}</td>
                                                    <td class="px-2 py-2">
                                                        <div class="font-bold">{{ $fan->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $fan->location }}</div>
                                                    </td>
                                                    <td class="px-2 py-2 text-center">{{ $fan->check_alarm_level_2 ? 'Tak' : 'Nie' }}</td>
                                                    <td class="px-2 py-2 text-center">{{ $fan->check_technical_condition === 'good' ? 'Poprawny' : 'Uszkodzony' }}</td>
                                                    <td class="px-2 py-2 text-center">{{ $fan->check_cables_condition === 'good' ? 'Poprawny' : 'Uszkodzony' }}</td>
                                                    <td class="px-2 py-2 text-center">{{ $fan->current_1 }}</td>
                                                    <td class="px-2 py-2 text-center">{{ $fan->current_2 }}</td>
                                                    <td class="px-2 py-2 text-center font-bold {{ $fan->result === 'positive' ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $fan->result === 'positive' ? 'Sprawne' : 'Niesprawne' }}
                                                    </td>
                                                    <td class="px-2 py-2">{{ $fan->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 p-4 bg-gray-100 rounded page-break-inside-avoid">
                                <h4 class="font-bold mb-2">Podsumowanie Wentylatorów</h4>
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>Liczba wentylatorów: <strong>{{ $stats['fans_total'] }}</strong></div>
                                    <div class="text-green-600">Sprawne: <strong>{{ $stats['fans_positive'] }}</strong></div>
                                    <div class="text-red-600">Niesprawne: <strong>{{ $stats['fans_negative'] }}</strong></div>
                                </div>
                            </div>
                        @endif

                        @if($protocol->system->slug === 'detekcja-gazow')
                            <!-- Centrale -->
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Centrale') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Nazwa/Model</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($centrals as $item)
                                                <tr>
                                                    <td class="px-3 py-2 font-medium text-gray-900">
                                                        <div>{{ $item->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item->location }}</div>
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Sprawna' : 'Niesprawna' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detektory -->
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Detektory') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Nazwa/Typ</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Następna Kalibracja</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($detectors as $item)
                                                <tr>
                                                    <td class="px-3 py-2 font-medium text-gray-900">
                                                        <div>{{ $item->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item->location }}</div>
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : ($item->result === 'calibration' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ $item->result === 'positive' ? 'Sprawny' : ($item->result === 'calibration' ? 'Do kalibracji' : 'Niesprawny') }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-500">
                                                        {{ $item->next_calibration_date ? $item->next_calibration_date->format('d.m.Y') : '-' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Urządzenia Sterujące -->
                            <div class="mt-8">
                                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">{{ __('Urządzenia Sterujące') }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Typ</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-500">Wynik</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-500">Uwagi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($controls as $item)
                                                <tr>
                                                    <td class="px-3 py-2 font-medium text-gray-900">
                                                        <div>{{ $item->type }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item->location }}</div>
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $item->result === 'positive' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $item->result === 'positive' ? 'Sprawne' : 'Niesprawne' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->notes }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-8 p-4 bg-gray-100 rounded page-break-inside-avoid">
                                <h4 class="font-bold mb-2">Podsumowanie Detektorów</h4>
                                <div class="grid grid-cols-4 gap-4 text-center">
                                    <div>Liczba detektorów: <strong>{{ $stats['detectors_total'] }}</strong></div>
                                    <div class="text-green-600">Sprawne: <strong>{{ $stats['detectors_positive'] }}</strong></div>
                                    <div class="text-yellow-600">Do kalibracji: <strong>{{ $stats['detectors_calibration'] }}</strong></div>
                                    <div class="text-red-600">Niesprawne: <strong>{{ $stats['detectors_negative'] }}</strong></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <a href="{{ route('protocols.step3', $protocol) }}" class="text-gray-600 hover:text-gray-900 underline">
                            {{ __('Wstecz') }}
                        </a>
                        <a href="{{ route('protocols.pdf', $protocol) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Podejrzyj PDF') }}
                        </a>
                        <a href="{{ route('protocols.download', $protocol) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Pobierz PDF') }}
                        </a>
                        <a href="{{ route('objects.show', $protocol->clientObject) }}" style="background-color: #16a34a !important; color: white !important;" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Zakończ') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
