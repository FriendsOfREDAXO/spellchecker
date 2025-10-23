<?php

/** @var rex_addon $this */

use Symfony\Component\Process\Process;

$func = rex_request('func', 'string');

if ('update' === $func) {
    $this->setConfig(
        rex_post(
            'spellchecker',
            [
                [
                    'binaryPath', 'string',
                ],
            ],
        ),
    );

    echo rex_view::success($this->i18n('settings_saved'));
}

// führe einen konsolenaufruf über symfony aus, um zu prüfen, ob der aspell-pfad korrekt ist. bitte nur wirklich vorhandene klassen verwenden
$binaryPath = $this->getConfig('binaryPath');

$aspellCheck = false;
if ($binaryPath) {
    $process = new Process([$binaryPath, '--version']);
    try {
        $process->run();

        if ($process->isSuccessful()) {
            $aspellCheck = true;
        }
    } catch (Exception $e) {
        $aspellCheckMessage = rex_view::error('Fehler beim Ausführen von Aspell: ' . rex_escape($e->getMessage()));
    }
}

if ($aspellCheck) {
    echo rex_view::info('Aspell-Pfad ist gültig: ' . rex_escape($binaryPath));
} else {
    echo rex_view::error('Aktueller Aspell-Pfad ist nicht gültig: ' . rex_escape($binaryPath));

    exec('which aspell', $output, $returnCode);

    if (0 === $returnCode && !empty($output)) {
        $aspellPath = $output[0];
        echo rex_view::info('Folgender Aspell-Pfad wurde automatisch gefunden: `' . rex_escape($aspellPath) . '`. Bitte diesen in den Einstellungen übernehmen.');
    } else {
        echo rex_view::error('Es wurde kein Aspell-Pfad automatisch gefunden');
    }
}

$content = '';

$formElements = [];

$n = [];
$n['label'] = '<label for="spellchecker-bin">' . rex_i18n::msg('spellchecker_bin_path') . '</label>';
$n['field'] = '<input class="form-control" id="spellchecker-bin" type="text" name="spellchecker[binaryPath]" value="' . rex_escape($this->getConfig('binaryPath')) . '" />';
$n['note'] = 'Default Aspell Pfad: `/usr/bin/aspell`';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-apply rex-form-aligned" type="submit" name="send" value="1"' . rex::getAccesskey(
        rex_i18n::msg('update'),
        'apply',
    ) . '>' . rex_i18n::msg('update') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('settings'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$section = $fragment->parse('core/page/section.php');

echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="update" />
        ' . $section . '
    </form>
';
