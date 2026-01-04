<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kreator Protokołu - Krok 3/4: Badanie Stanu') }}
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
                            <span class="font-bold text-indigo-600">Badanie stanu</span>
                            <span>Podgląd i PDF</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('protocols.store.step3', $protocol) }}">
                        @csrf

                        @if($protocol->system->slug === 'gasnice')
                            @include('protocols.systems.fire-extinguishers', ['extinguishers' => $protocolExtinguishers])
                        @elseif($protocol->system->slug === 'drzwi-przeciwpozarowe')
                            @include('protocols.systems.doors', ['protocolDoors' => $protocolDoors])
                        @elseif($protocol->system->slug === 'klapy-pozarowe')
                            @include('protocols.systems.fire-dampers', ['protocolDampers' => $protocolDampers])
                        @elseif($protocol->system->slug === 'system-oddymiania')
                            @include('protocols.systems.smoke-extraction', ['protocolSmokeSystems' => $protocolSmokeSystems])
                        @elseif($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne')
                            @include('protocols.systems.emergency-lighting', ['protocolLighting' => $protocolLighting])
                        @elseif($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu')
                            @include('protocols.systems.pwp', ['protocolPwpDevices' => $protocolPwpDevices])
                        @elseif($protocol->system->slug === 'bramy-i-grodzie-przeciwpozarowe')
                            @include('protocols.systems.fire-gates', ['protocolFireGateDevices' => $protocolFireGateDevices])
                        @else
                            <div class="text-center py-10">
                                <p class="text-gray-500">{{ __('Brak dedykowanego formularza dla tego systemu.') }}</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('protocols.step2', $protocol) }}" class="text-gray-600 hover:text-gray-900 underline">
                                {{ __('Wstecz') }}
                            </a>
                            <x-button class="ml-4">
                                {{ __('Dalej (Podgląd)') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
