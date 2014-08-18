<?php
require_once("ODBCHelper.php");
class Controller{
    public $db = null;

    function __construct(){
        $this->openDatabaseConnection();
    }

    public function print_dbg($obj){
        print('<pre>');
        print_r($obj);
        print('</pre>');
    }

    private function openDatabaseConnection(){
        $this->db = ODBCHelper::getInstance();
    }

    /* @param string $model_name The name of the model
     * @return object model
     */
    public function loadModel($model_name){
        require_once "application/".$model_name.'.php';
        return new $model_name();    
    }
}

?>