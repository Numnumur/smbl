<?php

namespace App\Helper;

trait RedirectToListPage
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
