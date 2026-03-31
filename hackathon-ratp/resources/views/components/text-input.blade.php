@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#004fa3] focus:ring-[#004fa3] rounded-lg shadow-sm text-sm']) }}>
