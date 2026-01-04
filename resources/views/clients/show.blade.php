<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Szczegóły Klienta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Dane Klienta -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Dane Klienta') }}</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('Nazwa') }}</span>
                                    <span class="block text-gray-900">{{ $client->name }}</span>
                                </div>
                                
                                @if($client->nip)
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('NIP') }}</span>
                                    <span class="block text-gray-900">{{ $client->nip }}</span>
                                </div>
                                @endif

                                @if($client->address)
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('Adres') }}</span>
                                    <span class="block text-gray-900">
                                        {{ $client->address }}<br>
                                        {{ $client->postal_code }} {{ $client->city }}
                                    </span>
                                </div>
                                @endif

                                @if($client->phone)
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('Telefon') }}</span>
                                    <span class="block text-gray-900">{{ $client->phone }}</span>
                                </div>
                                @endif

                                @if($client->email)
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('Email') }}</span>
                                    <a href="mailto:{{ $client->email }}" class="text-indigo-600 hover:text-indigo-900">{{ $client->email }}</a>
                                </div>
                                @endif

                                @if($client->notes)
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('Uwagi') }}</span>
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $client->notes }}</p>
                                </div>
                                @endif

                                <div>
                                    <span class="block text-sm font-medium text-gray-500">{{ __('Status') }}</span>
                                    @if($client->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Aktywny') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ __('Nieaktywny') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-200 pt-4 flex justify-end space-x-3">
                                <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Edytuj') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista Obiektów -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Obiekty Klienta') }}</h3>
                                <a href="{{ route('objects.create', ['client_id' => $client->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ __('+ Dodaj Obiekt') }}
                                </a>
                            </div>

                            @if($client->objects->isEmpty())
                                <p class="text-gray-500 text-sm">{{ __('Brak przypisanych obiektów.') }}</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('Nazwa') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('Adres') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('Status') }}
                                                </th>
                                                <th scope="col" class="relative px-6 py-3">
                                                    <span class="sr-only">{{ __('Akcje') }}</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($client->objects as $object)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">{{ $object->name }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $object->address }} <span class="text-gray-400">({{ $object->city }})</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($object->is_active)
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                {{ __('Aktywny') }}
                                                            </span>
                                                        @else
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                {{ __('Nieaktywny') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <div class="flex items-center justify-end space-x-3">
                                                            <a href="{{ route('objects.edit', $object) }}" class="text-indigo-600 hover:text-indigo-900" title="{{ __('Edytuj') }}">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
