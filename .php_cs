<?php

$header = <<<EOF
This file is part of the PHPCR API Tests package

Copyright (c) 2015 Liip and others

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

// Run the phpcsfixer from this directory to fix all code style issues
// https://github.com/FriendsOfPHP/PHP-CS-Fixer

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        'header_comment',
        '-psr0',
        'psr4',
        'symfony',
        'concat_without_spaces',
        '-phpdoc_indent',
        '-phpdoc_params',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
;
