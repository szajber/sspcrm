<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('Nazwa') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('Klient') }}
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
        @foreach ($objects as $object)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        <a href="{{ route('objects.show', $object) }}" class="hover:text-indigo-600 hover:underline">
                            {{ $object->name }}
                        </a>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($object->client)
                        <a href="{{ route('clients.show', $object->client) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">{{ $object->client->name }}</a>
                    @else
                        <span class="text-gray-400">{{ __('Brak') }}</span>
                    @endif
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
                        @if($object->is_active)
                            <form action="{{ route('objects.destroy', $object) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Czy na pewno chcesz dezaktywować ten obiekt?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="{{ __('Dezaktywuj') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-4 px-4 py-2">
    {{ $objects->links() }}
</div>
