<?php

namespace App\Filament\Seller\Widgets;

use App\Enums\ResponseStatus;
use App\Models\Response;
use DB;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ResponseChart extends ChartWidget
{

    protected static ?int $sort = 4;

    protected static bool $isDiscovered = false;
    protected static ?string $maxHeight = '340px';
    protected static ?string $pollingInterval = '350s';
    protected int|string|array $columnSpan = '2';

    public function getHeading(): string|Htmlable|null
    {
        return __('string.status') . ' ' . __('responses.Responses');
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],


            ],
            'scales' => [
                'x' => [
                    'display' => false // Hides the x-axis
                ],
                'y' => [
                    'display' => false // Hides the y-axis
                ]
            ]
        ];
    }

    protected function getData(): array
    {
        $groupedResponses = Response::query()
            ->where('seller_id', auth('seller')->id())
            ->select('responses.status', DB::raw('count(responses.id) as total_responses'))
            ->groupBy('responses.status')
            ->get();

        $labels = array_map(fn($status) => $status->getLabel(), ResponseStatus::cases());
        $colors = array_map(fn($status) => $status->getChartColor(), ResponseStatus::cases());
        $data = [];// array_map(fn($response) => $response['total_responses']??0, $groupedResponses);

        foreach (ResponseStatus::cases() as $status) {
            $data[] = $groupedResponses->firstWhere('status', $status)['total_responses'] ?? 0;

        }

        return [
            'datasets' => [
                [
                    'label' => __('responses.Responses'),
                    'data' => $data,
                    'showLine' => false,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
