<?php

namespace App\Helper;

use Illuminate\Contracts\Support\Htmlable;

trait PageCustomizing
{
    public function getTitle(): string | Htmlable
    {
        return static::$title;
    }

    public static function getNavigationLabel(): string
    {
        return static::$title;
    }
}
