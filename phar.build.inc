<?php
// config
$compiler->stripComments(true)->setShebang(true)->addSuffix('.json')->addExclude([
    'test',
    'tests',
    'runtime',
    'eaglewu',
])->addFile([
    'LICENSE',
    'README.md',
])->setCliIndex('bin/swoft');

// Command Controller 命令类不去除注释，注释上是命令帮助信息
$compiler->setStripFilter(function ($file) {
    /** @var \SplFileInfo $file */
    $path = $file->getPath();

    if (strpos($path, 'swoft')) {
        return false;
    }

    return false === strpos($file->getFilename(), 'Command.php');
});
