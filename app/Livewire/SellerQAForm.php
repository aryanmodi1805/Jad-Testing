<?php

namespace App\Livewire;

use App\Models\QA;
use App\Models\SellerQA;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Livewire\Component;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

class SellerQAForm extends Component implements Forms\Contracts\HasForms, HasActions
{
    use Forms\Concerns\InteractsWithForms, InteractsWithActions;

    public $qas;
    public $seller;

    public function mount()
    {
        $this->seller = auth('seller')->user();
        
        // Cache QA list for 24 hours (rarely changes)
        $qaList = \Cache::remember('qa_list', 60 * 60 * 24, function () {
            return QA::all();
        });
        
        // Load all seller QAs in ONE query instead of N+1 queries
        $sellerQAs = SellerQA::where('seller_id', $this->seller->id)
            ->pluck('answer', 'q_a_s_id');
        
        $this->qas = $qaList->map(function ($qa) use ($sellerQAs) {
            return [
                'qa_id' => $qa->id,
                'question' => $qa->name,
                'answer' => $sellerQAs[$qa->id] ?? '',
            ];
        })->toArray();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make(__('Q&A'))
                ->description(__('seller.q_a_s.text'))
                ->collapsible()
                ->inlineLabel()
                ->columnSpanFull()
                ->footerActions([
                    Forms\Components\Actions\Action::make('submit')
                        ->submit('submit')->label(__('filament-breezy::default.profile.personal_info.submit.label')),
                ])
                ->footerActionsAlignment(Alignment::End)
                ->schema([
            Repeater::make('qas')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('question')
                                ->label('Question')
                                ->readOnly()
                                ->default(fn ($record) => $record['question'] ?? ''),
                            Forms\Components\RichEditor::make('answer')
                                ->label('Answer')
                                ->required()
                                ->default(fn ($record) => $record['answer'] ?? ''),
                        ])
                ])->columnSpan(3)
                ->addable(false)
                ->orderColumn(false)
                ->deletable(false)

                ])
        ];
    }

    public function submit()
    {
        $this->validate();

        $formState = $this->form->getState();

        if (isset($formState['qas'])) {
            foreach ($formState['qas'] as $qaState) {
                SellerQA::updateOrCreate(
                    [
                        'q_a_s_id' => $qaState['qa_id'] ?? null,
                        'seller_id' => $this->seller->id
                    ],
                    ['answer' => $qaState['answer']]
                );
            }
        }
        $this->sendNotification();
    }

    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('Changes saved successfully'))
            ->send();
    }

    public function render()
    {
        return view('livewire.seller-qa-form', [
            'form' => $this->form,
        ]);
    }
}
