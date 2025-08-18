@props(['status'])

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
    @if($status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
    @elseif($status === 'accepted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
    @elseif($status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
    @elseif($status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
    {{ ucfirst($status) }}
</span>