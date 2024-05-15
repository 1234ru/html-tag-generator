<?php

require_once __DIR__ . '/HTMLtagGenerator.php';

$configs = [
    [
        'text' => 'This is some text',
        'attr' => [
            'id' => 'main',
            'class' => 'someclass',
            'style' => 'font-weight: bold',
            'data-something' => [ 'x' => 1, 'y' => 2 ]
        ]
    ],
    [
        'tag' => 'input',
        'attr' => [
            'type' => 'checkbox',
            'checked' => false,
        ]
    ],
    [
        'tag' => 'input',
        'attr' => [
            'type' => 'checkbox',
            'checked' => true
        ]
    ],
    [
        'text' => 'Did you like it?', // Вам понравилось?
        'children' => [
            [
                'tag' => 'input',
                'attr' => [
                    'type' => 'submit',
                    'value' => 'Yes!', // Да!
                ],
            ],
            [
                'tag' => 'input',
                'attr' => [
                    'type' => 'reset',
                    'value' => 'No', // Нет
                ]
            ],
            [
                'tag' => 'button',
                'text' => "I don't know", // Не знаю
            ]
        ]
    ],
    [
        'tag' => 'ul',
        'children' => [
            '<li>One</li>', // Раз
            '<li>Two</li>', // Два
            '<li>Three</li>', // Три
        ]
    ],
    [
        'children' => [
            'This is the text at the beginning. ', // Это текст в начале
            '<b>This is a bold text.</b> ', // Это жирный текст
            'This is the text at the end.', // Это текст в конце
        ]
    ]
];

echo "<ol>";
foreach ($configs as $cfg) {
    $html = new \One234ru\HTMLtagGenerator($cfg);
    echo "<li style='padding-bottom: 1em; margin-bottom: 1em; border-bottom: 1px solid;'>"
        ."<p style='font-weight: bold'>HTML:</p>"
        . "<div style='padding: 1em; background: #F8f8f8'>\n$html\n</div>"
        . "<p style='font-weight: bold'>HTML source:</p>"
        . "<pre style='padding: 1em; background: #F8f8f8'>\n" . htmlspecialchars($html) ."\n</pre>"
        . "<p>Config:</p>"
        . "<pre style='padding: 1em; background: #F8f8f8'>"
        . htmlspecialchars(var_export($cfg, true))
        . "</pre>"
        . "</li>";
}
echo "</ol>";
