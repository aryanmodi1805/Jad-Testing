<?php

namespace App\Traits;

trait HasFullNameTranslation
{


   public function initializeAppendAttributeTrait()
    {
        $this->append('full_name');
    }

    function getFullNameAttribute() : string {
        $name = collect($this->getTranslations($this->getNameColumn()));
        return $name->count()>1? $name->first().' - '.$name->last():$name->first();

    }

    public function getNameColumn() : string {
        return "name";
    }


}
