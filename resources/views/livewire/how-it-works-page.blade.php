<div class="min-h-screen w-full flex flex-col">

    @if($page == 'sellers')
    <livewire:how-it-works.seller-hero/>
    <livewire:how-it-works.seller-page/>
    @elseif($page == 'customers')
    <livewire:how-it-works.customer-hero/>
    <livewire:how-it-works.customer-page/>

    @endif



    <livewire:footer/>
</div>
