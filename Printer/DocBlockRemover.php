<?php
namespace PhpDocblockRemover\Printer;

use PhpParser\PrettyPrinter\Standard;

class DocBlockRemover extends Standard
{
    /**
     * @param array $comments
     * @return string
     */
    protected function pComments(array $comments)
    {
        return "";
    }

}
