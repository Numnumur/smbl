<?php

namespace App\Helper;

trait ResourceCustomizing
{
    public static function getModelLabel(): string
    {
        return static::$title;
    }

    public static function getNavigationIcon(): string
    {
        return static::$icon;
    }

    public static function getNavigationLabel(): string
    {
        return static::$title;
    }

    public static function getBreadcrumb(): string
    {
        return static::$title;
    }

    public static function getNavigationGroup(): string
    {
        return static::$group;
    }
}
