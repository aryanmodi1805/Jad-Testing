<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Question;
use App\Enums\AnswerType;

class BarkQuestionsSeeder extends Seeder
{
    public function run()
    {
        $ids = [
            '9bb800e9-7cca-48a5-b0a9-a5b7764a83d6',
            '9bb800eb-88a0-4a1d-a1bd-08819e7e0d99',
            '9bb800eb-8a67-4915-a268-33244f64975b',
            '9bb800eb-8c2b-4da1-ae80-640d1f1485e1',
            '9bb800ed-47fd-416d-9cd1-e369183f078c',
            '9bb800ee-f333-4760-8578-56ba96e0bd90',
            '9bb800ee-f65f-4356-8949-349b7959eb03',
            '9bb800ee-f950-4563-b742-3398ba3fd9bf',
            '9bb800f0-b637-42a4-96ee-b13f90f3b314',
            '9bb800f0-ba3f-45aa-ae5e-cd031df355e0',
            '9bb800f0-bcd9-4321-b3b9-b3193fe74a47',
            '9bb800f0-bf38-4057-afe1-33e4f5147f7b',
            '9bb800f2-6f1f-4be1-896b-6cb5492e705d',
            '9bb800f5-d580-470e-95b0-8f8ae59c1caa',
            '9bb800f5-dad9-4a02-82a2-9def91010c6b',
            '9bb800f5-dd72-4695-8799-11c4932620e0',
            '9bb800f7-935f-4a17-b0e8-95e6dd4eb1e1',
            '9bb800f9-513b-4bc1-a09b-cd6122326bdd',
            '9bb800fb-2f5f-4b4d-a32e-5f92a4bd8230',
            '9bb800fb-3377-4bec-bcfc-4854d5f0e431',
            '9bb800ff-011e-47fc-a679-13730980ed33',
            '9bb80100-3a24-4dca-bb26-8fde3a2c0744',
            '9bb80101-eb34-4e72-aa18-677546723312',
            '9bb80103-113f-492f-bafd-cfc7b0f0d703',
            '9bb80104-bebc-4662-ad49-cb0a413de0df',
            '9bb80106-72c9-46cd-9b33-6afc4f6250bd',
            '9bb80108-225f-45d5-bdda-74a9440d55a0',
            '9bb80109-d788-48c9-8c16-b008eedd6c02',
            '9bb8010d-49a4-4a5e-9739-b375f72386ed',
            '9bb8010f-029c-4e13-aa64-714bfee29b7b',
            '9bb80112-63c6-428e-9a16-af44b33ef136',
            '9bb80114-1521-42aa-a50d-3a724c88c170',
            '9bb80115-befa-4d28-9af9-214fab459d41',
            '9bb80117-6ca6-4c3d-a276-14ee20720ff2',
            '9bb80119-1f54-47a0-9814-ce8a7e933310',
            '9bb8011a-cee7-4bca-b53d-bc9945e152d9',
            '9bb8011c-7e1a-4b9b-878c-b256fe06c5b5',
            '9bb8011e-2b00-436d-af18-c93ac3fd1f54',
            '9bb8011f-dddc-4f68-928d-3fd8017c5703',
            '9bb80121-9517-4a80-b530-adca6b6aae9e',
            '9bb80123-49b7-4e0b-b0ac-2b257cee56f0',
            '9bb80123-4bc5-49f0-bc92-6fb981de4c24',
            '9bb80124-fa28-4e69-aed8-9d76187824e2',
            '9bb80126-aa58-4d19-bf23-defa6b7421ec',
            '9bb80128-4e56-46cd-9109-20caea540098',
            '9bb80129-f0cd-4519-98ee-1f0b67250710',
            '9bb80129-f4ab-47ce-b4b9-40b39e20d959',
            '9bb8012b-aeee-4830-8b64-ad0fcfafbf62',
            '9bb8012d-548d-46d3-a281-1d2187e79399',
            '9bb8012f-0f77-41be-a7c4-1120daed821c',
            '9bb80130-d315-4bc3-992c-12eb18dafe36',
            '9bb80132-838e-4ecc-a11e-b2419118fed3',
            '9bb80134-300f-45ff-bd6a-3a3c0d860ca8',
            '9bb80135-ea7f-4b39-a231-9f61f3f9fe6e',
            '9bb80135-ed4f-4187-9622-b8a14c8e153f',
            '9bb80135-efff-406e-981a-8420c4b34d4f',
            '9bb80137-a98d-49d3-a4b6-2c149a263467',
            '9bb80137-af96-4926-a4f8-b4ef77f06b4e',
            '9bb80139-6670-43ca-812d-d88e8f5d998c',
            '9bb8013c-ce75-4fee-ab12-156bfc874eff',
            '9bb8013e-7fa7-4fcc-90d2-f6aac4f945cf',
            '9bb80140-37d7-4931-b024-d393aaadf12d',
            '9bb80140-39dd-475e-a32b-190208b97e1f',
            '9bb80140-3c10-442b-b13b-f8f9b130f86d',
            '9bb80143-abfd-4430-838d-5b8237fdaa98',
            '9bb80145-c2d3-4b93-a067-e2a60eaf333c',
            '9bb80147-780e-4166-a89f-bca23ddcbaa2',
            '9bb80149-423d-4352-b0c8-848efc22e239',
            '9bb80149-48b8-4814-89b3-7cf9ce88ac2d',
            '9bb80149-4bdd-46fc-8dea-b2c6423e844b',
            '9bb8014c-a67f-471d-bd52-8c41b450ff67',
            '9bb8014e-5507-4eb6-b654-5619ddf78ed9',
            '9bb80150-0212-4442-b598-9f171e580c27',
            '9bb80151-2522-4470-b99c-da9badcd1d8d',
            '9bb80151-29c2-40a2-8f9d-1a9b8376b7ec',
            '9bb80151-2b4b-4279-b129-5564c3bb70d2',
            '9bb80151-2d00-4b82-931a-49f9a087fbe3',
            '9bb80151-2f13-4113-8056-1a59544930d7',
            '9bb80151-30b3-4e13-83e1-405f9b6eeb4c',
            '9bb80151-321d-43fc-a585-2f3e53d2210c',
            '9bb80151-338f-4582-8e9a-acfd9a85337b',
            '9bb80154-955e-4929-965f-1cebb4d4e24f',
            '9bb80156-3f30-4fae-b0d8-bd4fea978201',
            '9bb80156-4495-4885-8058-f15bcbefa079',
            '9bb80158-0a28-4153-ac00-9a0b31137553',
            '9bb80158-0f9c-4ecb-bfeb-bd8ad832e8fc',
            '9bb80158-11f0-41b3-8f43-2de2bf733b18',
            '9bb80158-1439-4583-b44d-0145eb506b1e',
            '9bb80158-16c9-400a-821a-6c7c28826c88',
            '9bb80158-1920-4ab9-8f2d-9d2c28073057',
            '9bb80158-1b68-49b6-a387-d385472af31e',
            '9bb80158-1d65-4062-af22-ed4d67723f03',
            '9bb80158-1f7a-4453-be5d-5b7805e7cb8f',
            '9bb80158-2150-4d01-8117-edab622a5c83',
            '9bb8015d-73d4-4984-b90c-10c61a0e0be3',
            '9bb80160-c9f0-427e-ad44-1c90fbb61d44',
        ];


        foreach ($ids as $id) {
            DB::beginTransaction();
            try {
                $service = \App\Models\Service::where('id',$id)->first();

                if (!$service) {
                    echo "Service not found for ID: {$id}\n";
                    DB::rollBack();
                    continue;
                }

                $header = array(
                    "Content-Type: application/json",
                    "x-channel: merchant",
                    "Accept: application/json"
                );
                $url = "https://api.bark.com/project-flow";

                $params = [
                    'category_id' => $service->bark_id,
                    'country_id' => '236',
                    'category_slug' => $service->slug,
                    'origin' => 'bnb-project-dash',
                ];

                $result = Http::withHeaders($header)
                    ->withoutVerifying()
                    ->timeout(300)
                    ->get($url, $params);

                $body = json_decode($result->body());
                if ($result->status() == 200) {
                    $all_data = $result->json('values');
                    $all_data = $all_data['categories'][$service->bark_id] ?? [];
                    $sort = 0;
                    if (!empty($all_data['custom_fields'])) {
                        foreach ($all_data['custom_fields'] as $data) {
                            if ($data['type'] == 'photoselect') {
                                $data['type'] = 'select';
                            }
                            if ($data['type'] != "postcode") {
                                $question = Question::updateOrCreate(
                                    ['label->en' => $data['label'], 'service_id' => $service->id],
                                    [
                                        'service_id' => $service->id,
                                        'label' => ['ar' => $data['label'], 'en' => $data['label']],
                                        'type' => $data['type'],
                                        'sort' => $sort,
                                    ]
                                );
                                $sort++;
                                if (empty($data['options'])) {
                                    $question->answers()->updateOrCreate(
                                        ['val' => 0],
                                    );
                                }
                                if (isset($data['options'])) {
                                    foreach ($data['options'] as $answer) {
                                        $is_custom = false;
                                        $type = $data['type'];
                                        if ($answer['label'] == 'Other') {
                                            $is_custom = true;
                                            $type = 'text';
                                        }
                                        $answerOption = [
                                            'label' => ['ar' => $answer['label'], 'en' => $answer['label']],
                                            'val' => 0,
                                            'is_custom' => $is_custom,
                                            'type' => AnswerType::getType($type),
                                            'image' => $answer['photo'] ?? null,
                                        ];
                                        $question->answers()->updateOrCreate(
                                            ['label->en' => $answerOption['label']['en']],
                                            $answerOption
                                        );
                                    }
                                }
                            }
                        }
                    } else {
                        echo "No Questions Found for service ID: {$id}\n";
                    }
                } else {
                    echo "Failed to fetch data for service ID: {$id}\n";
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error processing service ID: {$id}. Error: {$e->getMessage()}\n";
            }
        }
    }
}
