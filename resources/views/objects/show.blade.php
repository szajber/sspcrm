<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Szczegóły Obiektu') }}
            </h2>
            <div class="text-sm text-gray-500">
                @if($object->client)
                    {{ __('Klient:') }} <a href="{{ route('clients.show', $object->client) }}" class="text-indigo-600 hover:underline">{{ $object->client->name }}</a>
                @else
                    {{ __('Brak przypisanego klienta') }}
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Dane Obiektu -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $object->name }}</h3>
                            <div class="text-gray-600">
                                <p>{{ $object->address }}</p>
                                <p>{{ $object->postal_code }} {{ $object->city }}</p>
                            </div>
                            @if($object->notes)
                                <p class="mt-4 text-gray-500 text-sm whitespace-pre-wrap">{{ $object->notes }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end space-y-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $object->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $object->is_active ? __('Aktywny') : __('Nieaktywny') }}
                            </span>
                            <a href="{{ route('objects.edit', $object) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Edytuj') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Karty Systemów -->
            <h3 class="text-lg font-medium text-gray-900 px-1">{{ __('Systemy Ppoż') }}</h3>

            @if($object->systems->isEmpty())
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center text-gray-500">
                    {{ __('Brak aktywnych systemów dla tego obiektu.') }}
                    <a href="{{ route('objects.edit', $object) }}" class="text-indigo-600 hover:underline">{{ __('Aktywuj systemy w edycji obiektu.') }}</a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($object->systems as $system)
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-l-4 border-indigo-500">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800">{{ $system->name }}</h4>
                                    <!-- Link do tworzenia protokołu -->
                                    <a href="{{ route('protocols.create', [$object, $system]) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium hover:underline">
                                        {{ __('+ Nowy Protokół') }}
                                    </a>
                                </div>

                            <div class="space-y-3">
                                <p class="text-sm text-gray-500 italic">{{ __('Ostatnie protokoły:') }}</p>

                                @php
                                    $systemProtocols = $protocols->where('system_id', $system->id);
                                @endphp

                                @if($systemProtocols->isEmpty())
                                    <div class="text-sm text-gray-400 text-center py-4 bg-gray-50 rounded">
                                        {{ __('Brak protokołów') }}
                                    </div>
                                @else
                                    <div class="bg-white rounded border divide-y">
                                        @foreach($systemProtocols as $protocol)
                                            <div class="p-3 flex justify-between items-center text-sm hover:bg-gray-50">
                                                <div>
                                                    <a href="{{ route('protocols.preview', $protocol) }}" class="font-medium text-indigo-600 hover:underline">
                                                        {{ $protocol->number }}
                                                    </a>
                                                    <span class="text-gray-500 ml-2">({{ $protocol->date->format('d.m.Y') }})</span>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('protocols.edit', $protocol) }}" class="text-gray-400 hover:text-gray-600" title="{{ __('Edytuj') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    </a>
                                                    <a href="{{ route('protocols.pdf', $protocol) }}" target="_blank" class="text-gray-400 hover:text-red-600" title="{{ __('PDF') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-right mt-2">
                                        <a href="{{ route('protocols.index', [$object, $system]) }}" class="text-xs text-indigo-600 hover:underline">{{ __('Zobacz wszystkie') }} &rarr;</a>
                                    </div>
                                @endif
                            </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
