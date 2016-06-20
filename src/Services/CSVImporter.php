<?php namespace BadChoice\Grog\Services;

use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

class CSVImporter{
    public static function import($file, $callback){
        $config = new LexerConfig();
        $config->setDelimiter(";");

        $lexer          = new Lexer($config);
        $interpreter    = new Interpreter();
        $header         = null;

        $observer = new LexerAssociativeObserver();
        $observer->setCallback($callback);


        $interpreter->addObserver($observer->observer);
        $lexer->parse($file->getRealPath(), $interpreter);
    }

    public static function filterRow(&$row){
        // Fixes if prices with ,
        if(isset($row['price'])){
            $row['price'] = str_replace(",",".",$row['price']);
        }
        if(isset($row['photo']) && $row['photo'] == ''){
            $row['photo'] = null;
        }
        if(isset($row['dish_order_id']) && $row['dish_order_id'] == ''){
            $row['dish_order_id'] = null;
        }
    }
}
