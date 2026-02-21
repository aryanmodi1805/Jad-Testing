<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Forms\Components\Translatable;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use SolutionForest\FilamentTree\Actions\Action;
use SolutionForest\FilamentTree\Actions\ActionGroup;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;
use SolutionForest\FilamentTree\Widgets\Tree as BaseWidget;

class TreeCategoryWidget extends BaseWidget
{
    protected static string $model = Category::class;

    protected $listeners = ['updateTreeCategoryWidget' => '$refresh'];

    protected static int $maxDepth = 2;

    protected ?string $treeTitle = 'TreeCategoryWidget';

    protected bool $enableTreeTitle = false;

    protected function getFormSchema(): array
    {
        return [
            Translatable::make(),
            Toggle::make('active'),
        ];
    }

    // INFOLIST, CAN DELETE
  /*   public function getViewFormSchema(): array {
        return [
            TextInput::make('name'),
            Toggle::make('active'),
        ];
    } */

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }

    // CUSTOMIZE ACTION OF EACH RECORD, CAN DELETE
//    protected function getTreeActions(): array
//    {
//        return [
//            Action::make('helloWorld')
//                ->action(function () {
//                    Notification::make()->success()->title('Hello World')->send();
//                }),
//            // ViewAction::make(),
//          //   EditAction::make(),
//            ActionGroup::make([
//
//                ViewAction::make(),
//                EditAction::make(),
//            ]),
//            DeleteAction::make(),
//        ];
//    }
  //  OR OVERRIDE FOLLOWING METHODS
    //protected function hasDeleteAction(): bool
    //{
    //    return true;
    //}
//    protected function hasEditAction(): bool
//    {
//       return true;
//    }
    //protected function hasViewAction(): bool
    //{
    //    return true;
    //}
}
