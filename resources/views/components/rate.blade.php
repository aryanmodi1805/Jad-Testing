@props(['rating' => 0, 'totalReviews' => 0 , 'showRatingText' => false])

<div {{$attributes}}>
    <div class="flex items-center rtl:flex-row-reverse" style="direction: ltr">
        <div class="flex items-center">
            @foreach (range(1, 5) as $value)
                <div
                    @class([
                        "shrink-0 relative w-[0.625rem] h-5 overflow-hidden",
                        "text-slate-300" => $rating < ($value - 0.5),
                        "text-primary-500" => $rating >= ($value - 0.5),
                    ])
                >
                    <x-icon name="heroicon-s-star" class="absolute start-0 w-5 h-5"/>
                </div>

                <div
                    @class([
                        "shrink-0 relative w-[0.625rem] h-5 overflow-hidden",
                        "text-slate-300" => $rating < $value,
                        "text-primary-500" => $rating >= $value,
                    ])
                >
                    <x-icon name="heroicon-s-star" class="absolute end-0 w-5 h-5"/>
                </div>
            @endforeach
        </div>
        <div class="flex mx-2 gap-2 items-center place-items-center">
            <span class="mt-1">{{$showRatingText ? number_format($rating, 2) : ''}}</span>

            @unless(!$totalReviews)
                <span class="text-gray-500">({{$totalReviews}})</span>
            @endunless
        </div>

    </div>


</div>
