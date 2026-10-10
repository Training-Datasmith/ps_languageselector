<?php

class FixtureReset
{
    public static function resetAll()
    {
        Language::clearCatalog();
        Module::resetRegistry();
        Context::reset();
        InstallEventLog::reset();
        TranslatorRecorder::reset();
    }
}
