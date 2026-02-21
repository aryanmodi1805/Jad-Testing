<div class="space-y-4 container mx-auto">
    @foreach($faqs as $faq)

        <div x-data="{show:false}" class="bg-white p-8 shadow-lg">
            <button class="flex justify-between items-center w-full focus:outline-none" @click="show=!show">
                <span class="text-xl font-bold">{{$faq->question}}</span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="show" id="qa1" class="mt-4 text-lg text-gray-500 ltr:ltr rtl:rtl">
                {!! $faq->answer !!}
            </div>
        </div>
    @endforeach

   {{ $faqs->links() }}

</div>
