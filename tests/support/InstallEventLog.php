<?php

class InstallEventLog
{
    /** @var array */
    public static $events = array();

    /** @var bool */
    public static $parentInstallReturn = true;

    public static function reset()
    {
        self::$events = array();
        self::$parentInstallReturn = true;
    }
}
