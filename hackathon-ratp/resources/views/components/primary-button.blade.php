<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-[#004fa3] border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide hover:bg-[#1a63b6] active:bg-[#003d80] focus:outline-none focus:ring-2 focus:ring-[#004fa3] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
