<?php

declare(strict_types=1);

class rex_spellchecker_dictionary extends \rex_yform_manager_dataset
{
    private static $cacheWords = [];

    public static function getByWordLanguage($word, $language)
    {
        if (isset(self::$cacheWords[$word])) {
            return self::$cacheWords[$word];
        }

        $wordObject = self::query()->where('word', $word)->findOne();
        if (null === $wordObject) {
            $wordObject = self::create()
                ->setValue('word', $word)
                ->setValue('dic', 0)
                ->setValue('language', $language);
            $wordObject
                ->save();
        }

        self::$cacheWords[$word] = $wordObject;

        return $wordObject;
    }

    public function getWord()
    {
        return $this->getValue('word');
    }

    public function getLanguage()
    {
        return $this->getValue('language');
    }
}
