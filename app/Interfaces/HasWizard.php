<?php

namespace App\Interfaces;

interface HasWizard
{
    public function getCurrentWizardData(): array;
    public function setCurrentWizardData(array $data): void;



}
