<?php

$addon = rex_addon::get('spellchecker');

$func = rex_request('func', 'string');

if ('update' == $func) {
    $this->setConfig(
        rex_post(
            'spellchecker',
            [
                [
                    'binaryPath', 'string',
                ],
            ]
        )
    );

    echo rex_view::success($this->i18n('settings_saved'));
}

$content = '';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-lang">' . rex_i18n::msg('spellchecker_bin_path') . '</label>';
$n['field'] = '<input class="form-control" id="spellchecker-bin" type="text" name="spellchecker[binaryPath]" value="' . rex_escape($addon->getConfig('binaryPath')) . '" />';
$n['note'] = '/usr/bin/aspell';
$formElements[] = $n;

$fragment = new \rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-apply rex-form-aligned" type="submit" name="send" value="1"'.\rex::getAccesskey(
        \rex_i18n::msg('update'),
        'apply'
    ).'>'.\rex_i18n::msg('update').'</button>';
$formElements[] = $n;

$fragment = new \rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');



$fragment = new \rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('settings'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$section = $fragment->parse('core/page/section.php');

echo '
    <form action="'.\rex_url::currentBackendPage().'" method="post">
        <input type="hidden" name="func" value="update" />
        '.$section.'
    </form>
';
