<?php

namespace Database\Seeders;

use App\Models\Answer;
use Illuminate\Database\Seeder;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $answers = Answer::all();

        foreach ($answers as $answer) {
            if ($answer->getTranslation('label', 'en')) {
                $englishLabel = $answer->getTranslation('label', 'en');

                $translator = new GoogleTranslate();
                $translator->setSource('en');
                $translator->setTarget('ar');
                $translatedText = $translator->translate($englishLabel);

                $answer->setTranslation('label', 'ar', $translatedText);

                $this->command->info("English: " . $englishLabel . " | Arabic: " . $translatedText);


                $answer->save();
            }
        }
        $this->command->info('All answers have been translated!');
    }
}
