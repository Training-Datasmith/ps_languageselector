<?php

class LinkStub
{
    /**
     * @param int $idLang
     *
     * @return string
     */
    public function getLanguageLink($idLang)
    {
        return 'https://shop.test/lang/' . (int) $idLang;
    }
}
