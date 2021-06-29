<?php
/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title'       => 'Configuration Object',
    'description' => 'Transform any configuration plain array into a dynamic and configurable object structure, and pull apart configuration handling from the main logic of your script. Use provided services to add more functionality to your objects: cache, parents, persistence and much more.',
    'version'     => '3.0.0',
    'state'       => 'stable',
    'category'    => 'backend',

    'author'       => 'Romain Canon',
    'author_email' => 'romain.hydrocanon@gmail.com',

    'constraints' => [
        'depends'   => [
            'typo3' => '10.4.0-10.4.99',
            'php'   => '7.4.0-7.99.99'
        ],
        'conflicts' => [],
        'suggests'  => []
    ],

    'clearCacheOnLoad' => true
];
