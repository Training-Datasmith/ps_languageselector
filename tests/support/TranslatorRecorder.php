<?php

class TranslatorRecorder
{
    /** @var array */
    public static $calls = array();

    public static function reset()
    {
        self::$calls = array();
    }

    /**
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     * @param string|null $locale
     *
     * @return string
     */
    public static function trans($id, array $parameters, $domain, $locale)
    {
        self::$calls[] = array(
            'id' => $id,
            'parameters' => $parameters,
            'domain' => $domain,
            'locale' => $locale,
        );

        return $id;
    }
}
