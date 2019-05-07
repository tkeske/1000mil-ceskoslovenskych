<?php

namespace App\Utils;

use Latte;

class Macros extends Latte\Macros\MacroSet
{

    public static function install(Latte\Compiler $compiler)
    {
       $set = new static($compiler);

       $set->addMacro(
            'try', // název makra
            'try {',  // PHP kód nahrazující otevírací značku
            '} catch (\Exception $e) {}' // kód nahrazující uzavírací značku
        );

        return $set;
    }
}