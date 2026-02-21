<main class="h-fit relative">
    @for($i = 1; $i <= 4; $i++)
        <livewire:section
            title="{{$howItWorksSeller->getStepTitle($i,app()->getLocale())}}"
            content="{{$howItWorksSeller->getStepDescription($i,app()->getLocale())}}"
            image="{{$howItWorksSeller->getStepImage($i)}}"
            imagePosition="{{$i % 2 == 0 ? 'end' : 'start'}}"
            hidden="{{$howItWorksSeller->getStepTitle($i,app()->getLocale()) ? false : true}}"
        />
    @endfor

</main>
