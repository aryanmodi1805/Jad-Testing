<main class="h-fit relative">
    @for($i = 1; $i <= 4; $i++)
        <livewire:section
            title="{{$howItWorksCustomer->getStepTitle($i,app()->getLocale())}}"
            content="{{$howItWorksCustomer->getStepDescription($i,app()->getLocale())}}"
            image="{{$howItWorksCustomer->getStepImage($i)}}"
            imagePosition="{{$i % 2 == 0 ? 'end' : 'start'}}"
            hidden="{{$howItWorksCustomer->getStepTitle($i,app()->getLocale()) ? false : true}}"
        />
    @endfor

</main>
