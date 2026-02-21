<div class="container mx-auto rtl:lg:pr-20 ltr:lg:pl-20 py-16">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">{{ $title }}</h2>
        <div class="space-x-2 rtl:space-x-reverse">
            <button class="scroll-left bg-white rounded-full p-2 shadow-md rtl:transform rtl:rotate-180">
                <svg class="w-6 h-6 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L8.414 11l4.293 4.293a1 1 0 01-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <button class="scroll-right bg-white rounded-full p-2 shadow-md rtl:transform rtl:rotate-180">
                <svg class="w-6 h-6 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L11.586 11 7.293 6.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>
    <div class="scrollable-container flex overflow-x-scroll space-x-4 pb-4 rtl:space-x-reverse">
        {{ $slot }}
    </div>
</div>

<script>
    document.querySelectorAll('.scroll-left').forEach(button => {
        button.addEventListener('click', function() {
            const container = button.closest('.container').querySelector('.scrollable-container');
            const direction = document.documentElement.dir === 'rtl' ? 300 : -300;
            container.scrollBy({
                left: direction,
                behavior: 'smooth'
            });
        });
    });

    document.querySelectorAll('.scroll-right').forEach(button => {
        button.addEventListener('click', function() {
            const container = button.closest('.container').querySelector('.scrollable-container');
            const direction = document.documentElement.dir === 'rtl' ? -300 : 300;
            container.scrollBy({
                left: direction,
                behavior: 'smooth'
            });
        });
    });
</script>
