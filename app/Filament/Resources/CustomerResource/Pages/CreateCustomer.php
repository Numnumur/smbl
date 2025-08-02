<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Actions\Concerns\HasWizard;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use RedirectToListPage, HasWizard;

    protected static string $resource = CustomerResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
        ];
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->submitAction($this->getSubmitFormAction()),
            ])
            ->columns(null);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('name_and_user')
                ->label('Nama dan Akun Pengguna untuk pelanggan')
                ->schema(CustomerResource::getUserForm())
                ->columns(),
            Step::make('customer_details')
                ->label('Detail Pelanggan')
                ->schema(CustomerResource::getCustomerForm()),
        ];
    }
}
