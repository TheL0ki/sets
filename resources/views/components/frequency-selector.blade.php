@props(['name', 'value' => 1, 'min' => 1, 'max' => 10, 'step' => 1])

<div class="frequency-selector">
    <div class="flex items-center space-x-4">
        <input type="range" 
               name="{{ $name }}" 
               min="{{ $min }}" 
               max="{{ $max }}" 
               step="{{ $step }}" 
               value="{{ $value }}"
               class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
               {{ $attributes->merge(['class' => '']) }}>
        <span class="frequency-display text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[3rem] text-center">
            {{ $value }}
        </span>
    </div>
    
    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
        <span>{{ $min }}</span>
        <span>{{ $max }}</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.querySelector('input[name="{{ $name }}"]');
    const display = selector?.closest('.frequency-selector')?.querySelector('.frequency-display');
    
    if (selector && display) {
        selector.addEventListener('input', function() {
            display.textContent = this.value;
        });
    }
});
</script>
