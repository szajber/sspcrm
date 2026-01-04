<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kreator Protokołu - Krok 2/4: Lista Urządzeń') }}
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
                            <span class="font-bold text-indigo-600">Lista urządzeń</span>
                            <span>Badanie stanu</span>
                            <span>Podgląd i PDF</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 50%"></div>
                        </div>
                    </div>

                    @if($protocol->system->slug === 'gasnice')
                        <div class="mb-6">
                            @livewire('protocol-fire-extinguishers-manager', ['protocol' => $protocol])
                        </div>
                    @elseif($protocol->system->slug === 'drzwi-przeciwpozarowe')
                        <div class="mb-6">
                            @livewire('protocol-doors-manager', ['protocol' => $protocol])
                        </div>
                    @elseif($protocol->system->slug === 'klapy-pozarowe')
                        <div class="mb-6">
                            @livewire('protocol-fire-dampers-manager', ['protocol' => $protocol])
                        </div>
                    @elseif($protocol->system->slug === 'system-oddymiania')
                        <div class="mb-6">
                            @livewire('protocol-smoke-extraction-manager', ['protocol' => $protocol])
                        </div>
                    @elseif($protocol->system->slug === 'oswietlenie-awaryjne-i-ewakuacyjne')
                        <div class="mb-6">
                            @livewire('protocol-emergency-lighting-manager', ['protocol' => $protocol])
                        </div>
                    @elseif($protocol->system->slug === 'przeciwpozarowy-wylacznik-pradu')
                        <div class="mb-6">
                            @livewire('protocol-pwp-manager', ['protocol' => $protocol])
                        </div>
                    @else
                        <div class="text-center py-10">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Dane szczegółowe systemu') }}</h3>
                                <p class="text-gray-500 mt-2">{{ __('(Formularz specyficzny dla systemu: ') . $protocol->system->name . __(' - w budowie)') }}</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('protocols.edit', $protocol) }}" class="text-gray-600 hover:text-gray-900 underline">
                                {{ __('Wstecz') }}
                            </a>
                            <form method="POST" action="{{ route('protocols.store.step2', $protocol) }}">
                                @csrf
                                <x-button class="ml-4">
                                    {{ __('Dalej (Badanie Stanu)') }}
                                </x-button>
                            </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
