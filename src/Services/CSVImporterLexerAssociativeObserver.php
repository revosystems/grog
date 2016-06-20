<?php namespace BadChoice\Grog\Services;

class CSVImporterLexerAssociativeObserver{

    protected $header;
    public $observer;
    protected $callback;

    public function __construct(){
        $this->header = null;
        $this->observer = function(array $row){
            if($this->header == null) $this->header = $row;
            else {
                $associative_row = [];
                $i = 0;
                $isEmpty = true;
                foreach($this->header as $key){
                    if($row[$i] != "") $isEmpty = false;
                    $associative_row[$key] = $row[$i];
                    $i++;
                }
                if(!$isEmpty) {
                    call_user_func($this->callback, $associative_row);
                }
            }
        };
    }

    public function setCallback($callback){
        $this->callback = $callback;
    }

}

?>