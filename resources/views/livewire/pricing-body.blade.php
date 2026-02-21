<div>
    @for($i = 1; $i <= 4; $i++)
        <livewire:section
            title="{{$pricing->getStepTitle($i,app()->getLocale())}}"
            content="{{$pricing->getStepDescription($i,app()->getLocale())}}"
            image="{{$pricing->getStepImage($i)}}"
            imagePosition="{{$i % 2 == 0 ? 'end' : 'start'}}"
            hidden="{{$pricing->getStepTitle($i,app()->getLocale()) ? false : true}}"
        />
    @endfor
</div>
