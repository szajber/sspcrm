<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif; /* Supports Polish characters */
            font-size: 12px;
            color: #333;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
        }
        .logo {
            float: left;
            width: 150px;
        }
        .company-data {
            float: right;
            text-align: right;
            font-size: 10px;
        }
        .clear {
            clear: both;
        }
        .title-section {
            text-align: center;
            margin-bottom: 40px;
            margin-top: 100px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .protocol-number {
            font-size: 16px;
            font-family: monospace;
            font-weight: bold;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-col {
            width: 45%;
            float: left;
        }
        .info-col-right {
            width: 45%;
            float: right;
        }
        .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }
        .page-break {
            page-break-after: always;
        }
        .content-section {
            margin-bottom: 20px;
        }
        .footer-signatures {
            margin-top: 50px;
            height: 150px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            float: right;
            text-align: center;
        }
        .creator-box {
            width: 45%;
            float: left;
            text-align: left;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .signature-img {
            max-height: 60px;
            display: block;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>

    <!-- Page 1 -->
    <div class="header">
        <div class="logo">
            @if(isset($company) && $company->logo_path && file_exists(storage_path('app/public/' . $company->logo_path)))
                <img src="{{ storage_path('app/public/' . $company->logo_path) }}" style="max-width: 150px; max-height: 50px;">
            @else
                <strong>{{ $company->name ?? 'LOGO' }}</strong>
            @endif
        </div>
        <div class="company-data">
            <strong>{{ $company->name ?? 'Nazwa Firmy' }}</strong><br>
            {{ $company->address ?? '' }}<br>
            {{ $company->postal_code ?? '' }} {{ $company->city ?? '' }}<br>
            @if(isset($company->phone)) Tel: {{ $company->phone }}<br> @endif
            @if(isset($company->email)) Email: {{ $company->email }} @endif
        </div>
        <div class="clear"></div>
    </div>

    <div class="title-section">
        <h1>{{ $template->title ?? 'PROTOKÓŁ PRZEGLĄDU' }}</h1>
        <div class="protocol-number">{{ $protocol->number }}</div>
    </div>

    <div class="info-section">
        <div class="info-col">
            <div class="label">Zamawiający</div>
            @if($protocol->clientObject->client)
                <strong>{{ $protocol->clientObject->client->name }}</strong><br>
                {{ $protocol->clientObject->client->address }}<br>
                {{ $protocol->clientObject->client->postal_code }} {{ $protocol->clientObject->client->city }}
            @else
                <strong>Brak przypisanego klienta</strong>
            @endif
        </div>
        <div class="info-col-right">
            <div class="label">Obiekt</div>
            <strong>{{ $protocol->clientObject->name }}</strong><br>
            {{ $protocol->clientObject->address }}<br>
            {{ $protocol->clientObject->postal_code }} {{ $protocol->clientObject->city }}
        </div>
        <div class="clear"></div>
    </div>

    <div class="info-section">
        <div class="info-col">
            <div class="label">Data Wykonania</div>
            {{ $protocol->date->format('d.m.Y') }}
        </div>
        <div class="info-col-right">
            <div class="label">Data Następnego Przeglądu</div>
            @if($protocol->next_date)
                {{ $protocol->next_date->format('m.Y') }}
            @else
                Nie dotyczy
            @endif
        </div>
        <div class="clear"></div>
    </div>

    <div class="page-break"></div>

    <!-- Page 2: Description -->
    <div class="content-section">
        <div class="label">Informacje Ogólne</div>
        <div>
            {!! $template->description !!}
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Page 3: System Report -->
    <div class="content-section">
        <div class="label">Raport Szczegółowy</div>

        @if($protocol->system->slug === 'drzwi-przeciwpozarowe')
            @php
                $doors = $protocol->doors()->orderBy('id')->get();
                $stats = [];
                $statuses = [
                    'sprawne' => 'Sprawne',
                    'niesprawne' => 'Niesprawne'
                ];

                foreach ($doors as $door) {
                    $type = $door->resistance_class ?? 'Brak klasy';
                    if (!isset($stats[$type])) {
                        $stats[$type] = array_fill_keys(array_keys($statuses), 0);
                        $stats[$type]['total'] = 0;
                    }
                    $stats[$type][$door->status]++;
                    $stats[$type]['total']++;
                }

                $totals = array_fill_keys(array_keys($statuses), 0);
                $totals['total'] = 0;
                foreach ($stats as $typeStats) {
                    foreach ($typeStats as $key => $val) {
                        if ($key !== 'total') {
                             $totals[$key] += $val;
                        }
                    }
                    $totals['total'] += $typeStats['total'];
                }
            @endphp

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Lp.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Klasa odporności</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Lokalizacja</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Stan</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Uwagi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($doors as $index => $item)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->resistance_class }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->location }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">
                                {{ $statuses[$item->status] ?? $item->status }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="page-break"></div>

            <div class="label">Podsumowanie Ilościowe</div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Klasa odporności</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Ilość (Suma)</th>
                        @foreach($statuses as $key => $label)
                            <th style="border: 1px solid #ddd; padding: 5px;">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats as $type => $data)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $type }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center; font-weight: bold;">{{ $data['total'] }}</td>
                            @foreach($statuses as $key => $label)
                                <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                    {{ $data[$key] > 0 ? $data[$key] : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td style="border: 1px solid #ddd; padding: 5px;">SUMA</td>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $totals['total'] }}</td>
                        @foreach($statuses as $key => $label)
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                {{ $totals[$key] > 0 ? $totals[$key] : '-' }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>

            <div class="content-section">
                <div class="label">Uwagi Końcowe</div>
                <div style="border: 1px solid #ddd; padding: 10px; min-height: 50px;">
                    {{ $protocol->data['final_notes'] ?? 'Brak uwag.' }}
                </div>
            </div>

        @elseif($protocol->system->slug === 'klapy-pozarowe')
            @php
                $dampers = $protocol->fireDampers()->orderBy('id')->get();
                $stats = [
                    'total' => $dampers->count(),
                    'positive' => $dampers->where('result', 'positive')->count(),
                    'negative' => $dampers->where('result', 'negative')->count(),
                ];
            @endphp

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Lp.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Typ</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Lokalizacja</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Opt.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Napęd</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Mech.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Alarm</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Wynik</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Uwagi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dampers as $index => $item)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->type_name }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->location }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $item->check_optical ? 'Tak' : 'Nie' }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $item->check_drive ? 'Tak' : 'Nie' }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $item->check_mechanical ? 'Tak' : 'Nie' }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $item->check_alarm ? 'Tak' : 'Nie' }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                {{ $item->result === 'positive' ? 'Pozytywny' : 'Negatywny' }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="page-break"></div>

            <div class="label">Podsumowanie</div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Liczba klap</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Wynik Pozytywny</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Wynik Negatywny</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center; font-weight: bold;">{{ $stats['total'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center; color: green; font-weight: bold;">{{ $stats['positive'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center; color: red; font-weight: bold;">{{ $stats['negative'] }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="content-section">
                <div class="label">Uwagi Końcowe</div>
                <div style="border: 1px solid #ddd; padding: 10px; min-height: 50px;">
                    {{ $protocol->data['final_notes'] ?? 'Brak uwag.' }}
                </div>
            </div>

        @elseif($protocol->system->slug === 'oddymianie')
            @php
                $smokeSystems = $protocol->smokeExtractionSystems()->orderBy('id')->get();
                $stats = [
                    'total' => $smokeSystems->count(),
                    'positive' => $smokeSystems->where('result', 'positive')->count(),
                    'negative' => $smokeSystems->where('result', 'negative')->count(),
                ];
            @endphp

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Lp.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Centrala</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Lokalizacja</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Elementy systemu</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Data akumulatorów</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Wynik</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Uwagi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($smokeSystems as $index => $item)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->central_type_name }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->location }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">
                                Czujki: {{ $item->detectors_count }}<br>
                                Przyciski: {{ $item->buttons_count }}<br>
                                Przewietrzanie: {{ $item->vent_buttons_count }}<br>
                                Klapy/Went. napow.: {{ $item->air_inlet_count }}<br>
                                Klapy/Went. oddym.: {{ $item->smoke_exhaust_count }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->battery_date }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                {{ $item->result === 'positive' ? 'Pozytywny' : 'Negatywny' }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="page-break"></div>

            <div class="label">Podsumowanie</div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Liczba systemów</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Wynik Pozytywny</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Wynik Negatywny</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center; font-weight: bold;">{{ $stats['total'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center; color: green; font-weight: bold;">{{ $stats['positive'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center; color: red; font-weight: bold;">{{ $stats['negative'] }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="content-section">
                <div class="label">Uwagi Końcowe</div>
                <div style="border: 1px solid #ddd; padding: 10px; min-height: 50px;">
                    {{ $protocol->data['final_notes'] ?? 'Brak uwag.' }}
                </div>
            </div>

        @elseif($protocol->system->slug === 'gasnice')
            @php
                $extinguishers = $protocol->fireExtinguishers()->orderBy('id')->get();
                // Group stats
                $stats = [];
                $statuses = [
                    'legalizacja' => 'Legalizacja',
                    'remont' => 'Do remontu',
                    'zlom' => 'Do złomowania',
                    'brak' => 'Brak',
                    'po_remoncie' => 'Po remoncie',
                    'nowa' => 'Nowa'
                ];

                foreach ($extinguishers as $ext) {
                    $type = $ext->type_name ?? 'Inny';
                    if (!isset($stats[$type])) {
                        $stats[$type] = array_fill_keys(array_keys($statuses), 0);
                        $stats[$type]['total'] = 0;
                    }
                    $stats[$type][$ext->status]++;
                    $stats[$type]['total']++;
                }

                // Sumy
                $totals = array_fill_keys(array_keys($statuses), 0);
                $totals['total'] = 0;
                foreach ($stats as $typeStats) {
                    foreach ($typeStats as $key => $val) {
                        $totals[$key] += $val;
                    }
                }
            @endphp

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Lp.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Typ</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Lokalizacja</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Stan</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Rok nast. rem.</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Uwagi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($extinguishers as $index => $item)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->type_name }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->location }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px;">
                                {{ $statuses[$item->status] ?? $item->status }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                {{ $item->next_service_year ?? '-' }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $item->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="page-break"></div>

            <div class="label">Podsumowanie Ilościowe</div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 5px;">Typ gaśnicy</th>
                        <th style="border: 1px solid #ddd; padding: 5px;">Ilość (Suma)</th>
                        @foreach($statuses as $key => $label)
                            <th style="border: 1px solid #ddd; padding: 5px;">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats as $type => $data)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 5px;">{{ $type }}</td>
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center; font-weight: bold;">{{ $data['total'] }}</td>
                            @foreach($statuses as $key => $label)
                                <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                    {{ $data[$key] > 0 ? $data[$key] : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td style="border: 1px solid #ddd; padding: 5px;">SUMA</td>
                        <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">{{ $totals['total'] }}</td>
                        @foreach($statuses as $key => $label)
                            <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                {{ $totals[$key] > 0 ? $totals[$key] : '-' }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>

            <div class="content-section">
                <div class="label">Uwagi Końcowe</div>
                <div style="border: 1px solid #ddd; padding: 10px; min-height: 50px;">
                    <!-- Placeholder na uwagi edytowalne w przyszłości, na razie puste lub z pola data -->
                    {{ $protocol->data['final_notes'] ?? 'Brak uwag.' }}
                </div>
            </div>

        @else
            <p>Szczegółowe wyniki przeglądu systemu {{ $protocol->system->name }}...</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- Last Page: Summary & Signatures -->
    <div class="content-section">
        <div class="label">Podsumowanie</div>
        <p>System sprawny / niesprawny (placeholder)</p>
    </div>

    <div class="footer-signatures">
        <div class="creator-box">
            <div class="label">Protokół sporządził:</div>
            {{ Auth::user()->name }}<br>
            {{ Auth::user()->job_title }}
        </div>

        <div class="signature-box">
            <div class="label">Przegląd wykonał:</div>
            <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                @if($protocol->performer->signature_path && file_exists(storage_path('app/public/' . $protocol->performer->signature_path)))
                    <img src="{{ storage_path('app/public/' . $protocol->performer->signature_path) }}" class="signature-img" style="max-height: 60px;">
                @else
                    <div style="height: 60px;"></div>
                @endif
            </div>
            <div class="signature-line">
                {{ $protocol->performer->name }}<br>
                {{ $protocol->performer->job_title }}
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $x = 250;
            $y = 820;
            $text = "{{ $protocol->number }} - Strona {PAGE_NUM} z {PAGE_COUNT}";
            $font = null;
            $size = 10;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>
