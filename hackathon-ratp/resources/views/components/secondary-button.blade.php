<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2.5 bg-white border border-[#004fa3] rounded-lg font-semibold text-sm text-[#004fa3] tracking-wide hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-[#004fa3] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
