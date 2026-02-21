<?php

namespace App\Filament\Resources\ServiceResource\Actions;

use App\Models\Service;
use DB;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;

class getBarkServicesAction extends Action
{

//    protected Model|Closure|null $record = null;


    public static function getDefaultName(): ?string
    {
        return 'get bark services';
    }


    protected function setUp(): void
    {
        parent::setUp();


        $this->action(function () {
            $tenant = Filament::getTenant()?->id ?? null;


            $service = DB::table('services_1')->get();
            $header = array(
                "Content-Type: application/json",
                "x-channel: merchant",
                "Accept: application/json"
            );
            $url = "https://api.bark.com/services";
            $rows = array();

            foreach ($service as $item) {
                $params = [
                    'term' => $item->name,
                    'coid' => '236'
                ];

                $result = Http::withHeaders($header)
                    ->withoutVerifying()
                    ->timeout(300)
                    ->get($url, $params);
                $body = json_decode($result->body());
                if ($result->status() == 200 && $body->status == true) {
                    $all_data = $result->json('data');
                    foreach ($all_data as $data) {
                        $row = [
                            'bark_id' => $data['id']??0,
                            'name' =>  ['ar'=>$data['name'],'en'=>$data['name']],
                            'is_nationwide' => $data['is_nationwide'],
                            'is_remote' => $data['is_remote'],
                            'slug' => $data['name_url'],
                            'country_id' => $tenant,
                        ];
                        $cond = ['slug' => $row['slug'], 'bark_id' => $data['id']];
                        Service::updateOrCreate($cond, $row);

                    }
                    $all_data = [];
                }
            }
//            Service::Upsert($rows, ['name', 'bark_id']);
            $this->success();
        })
            ->label(__('Get Bark services'));
    }
}
