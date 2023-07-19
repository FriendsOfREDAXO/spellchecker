<?php

declare(strict_types=1);

use Mekras\Speller\Aspell\Aspell;
use Mekras\Speller\Dictionary;
use Mekras\Speller\Exception\PhpSpellerException;
use Mekras\Speller\Source\StringSource;

class rex_spellchecker_scan
{
    private static $scans = [];
    private $scanKey;
    private $idField;
    private $titleField;
    private $table;
    private $scanFields;
    private $query;
    private $query_count;
    private $link;
    private $language;
    private $scan_interval;
    private $scan_duration;

    public function __construct(string $scanKey, string $idField, string $titleField, string $table, array $scanFields, string $query, string $query_count, string $link, string $language, int $interval = 100, int $duration = 86400)
    {
        $this->scanKey = $scanKey;
        $this->idField = $idField;
        $this->titleField = $titleField;
        $this->table = $table;
        $this->scanFields = $scanFields;
        $this->query = $query;
        $this->query_count = $query_count;
        $this->link = $link;
        $this->language = $language;
        $this->scan_interval = $interval;
        $this->scan_duration = $duration; // Sekunden, danach startet der Scan wieder bei 0
    }

    /** @api */
    public static function addScan(self $scan): void
    {
        self::$scans[$scan->getKey()] = $scan;
    }

    /**
     * @return rex_spellchecker_scan[]
     */
    public static function getScans(): array
    {
        return self::$scans;
    }

    public static function scan(): bool
    {
        foreach (self::getScans() as $scan) {
            /** @var rex_spellchecker_scan $scan */
            //             $scan->resetConfig();
            if (!$scan->scanDone()) {
                foreach ($scan->getLimitedItemsAsArray() as $item) {
                    // Spellchecker filter drüberlaufen lassen
                    // dump($item);

                    $content = [];
                    foreach ($scan->getScanFields() as $field) {
                        $content[] = $item[$field];
                    }

                    $content = implode(' ', $content);
                    $content = strip_tags($content);
                    $content = str_replace('<', ' <', $content);

                    $source = new StringSource($content, 'utf-8');

                    $de = new Dictionary(rex_spellchecker::getDictionaryPath($scan->language));

                    $speller = new Aspell(rex_config::get('spellchecker', 'binaryPath', '/usr/bin/aspell'));
                    $speller->setPersonalDictionary($de);

                    try {
                        $issues = $speller->checkText($source, [$scan->language]);
                    } catch (PhpSpellerException $e) {
                        echo $e->getMessage();

                        return false;
                    }

                    rex_spellchecker_issue::deleteItemIssues($scan, $item);
                    $issueWords = [];
                    foreach ($issues as $issue) {
                        if ('Unknown word' === $issue->code && !in_array($issue->word, $issueWords, true)) {
                            $wordObject = rex_spellchecker_dictionary::getByWordLanguage($issue->word, $scan->language);
                            if (0 === (int) $wordObject->getValue('dic')) {
                                rex_spellchecker_issue::addIssue($wordObject, $scan, $item, $issue);
                            }
                            // extra - so dass nur einmal eine Wortfindung pro Item gespeichert wird.
                            $issueWords[] = $issue->word;
                        }
                    }
                }

                $scan->increaseCurrentScanPosition();
            }
        }

        return true;
    }

    public function getKey()
    {
        return $this->scanKey;
    }

    public function getInterval()
    {
        return $this->scan_interval;
    }

    public function getDuration()
    {
        return $this->scan_duration;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getIdField()
    {
        return $this->idField;
    }

    public function getTitleField()
    {
        return $this->titleField;
    }

    public function getTableName()
    {
        return $this->table;
    }

    /** @phpstan-ignore-next-line */
    public function getQuery()
    {
        return $this->query;
    }

    /** @phpstan-ignore-next-line */
    public function getQueryCount()
    {
        return $this->query_count;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getScanFields()
    {
        return $this->scanFields;
    }

    public function scanDone()
    {
        return !($this->getCurrentScanPosition() < $this->getCount());
    }

    public function resetConfig(): void
    {
        rex_config::set('spellchecker', 'csp_'.md5($this->getKey()), 0);
        rex_config::set('spellchecker', 'start_'.md5($this->getKey()), date('U'));
    }

    public function getCurrentScanPosition()
    {
        $this->initScan();

        return rex_config::get('spellchecker', 'csp_'.md5($this->getKey()), 0);
    }

    private function getLimitedItemsAsArray()
    {
        $CurrentScanPosition = $this->getCurrentScanPosition();
        /** @phpstan-ignore-next-line */
        return rex_sql::factory()->getArray($this->query . ' LIMIT ' . (int) $CurrentScanPosition . ',' . $this->scan_interval);
    }

    private function initScan(): void
    {
        $scanStart = rex_config::get('spellchecker', 'start_'.md5($this->getKey()).'', date('U'));
        if ((int) ($scanStart + $this->scan_duration) < (int) date('U')) {
            // abgelaufen = time setzen auf aktuell und currentPos auf 0
            $this->resetConfig();
        }
    }

    private function increaseCurrentScanPosition(): void
    {
        $currentScanPosition = rex_config::get('spellchecker', 'csp_'.md5($this->getKey()), 0) + $this->scan_interval;
        rex_config::set('spellchecker', 'csp_'.md5($this->getKey()), $currentScanPosition);
    }

    private function getCount()
    {
        return rex_sql::factory()->setQuery($this->query_count)->getValue('count');
    }
}
