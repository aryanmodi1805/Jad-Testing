<div class="flex gap-6 items-center mx-6 max-md:hidden">

    <a class="hover:text-secondary-500" href="{{ auth('seller')->check() ? '/seller/' : '/seller/login' }}">
        {{auth('seller')->check() ? __('auth.switch_to_seller_dashboard') : __('auth.login_as_pro')}}

    </a>

    <a href="{{auth('customer')->check() ? '/customer/' : '/customer/login'}}" class="flex h-fit">


        <div
            class="w-fit text-white border-b-[40px] border-b-[#0c2371] border-l-[25px] border-l-transparent border-r-[25px] border-r-transparent h-0 -me-[1.2rem]">
            <div class="flex justify-center items-center px-4 mt-[10px]">
                {{auth('customer')->check() ? __('auth.customer_dashboard') : __('auth.login')}}
            </div>
        </div>
        <svg width="50" height="40">
            <polygon points="25, 40, 0, 0, 50, 0" fill="#2547b6"/>
        </svg>
    </a>
</div>
