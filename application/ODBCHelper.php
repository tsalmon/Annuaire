<?php
class ODBCHelper {
    private static $odbc=null;

    //singleton
    private function __construct() {}

    public static function getInstance(){
        if(ODBCHelper::$odbc==null){
            if(!(ODBCHelper::$odbc=odbc_connect("Driver={SQL Server};Server=".DB_HOST.";Database=".DB_NAME."; CharacterSet => UTF-8", DB_USER, DB_PASS))) {
                echo "error";
                exit;
            }    
        }
        return ODBCHelper::$odbc;
    }

    public static function exec($query){
        $ret_exec = null;
        try{
            $ret_exec = odbc_exec(ODBCHelper::$odbc, $query);
        } catch (Exception $e){
            echo "<pre>";
            print_r($e->getMessage());
            echo "</pre>";
        }
        return $ret_exec;
    }

    public static function fetch($result_request){
        $fetch = odbc_fetch_array($result_request);
        if(!$fetch){
            return $fetch;
        }
        foreach ($fetch as $key => $value) {
            if(is_string($value)){
                $fetch[$key] = utf8_encode($value);
            }

        }
        return $fetch;
    }

    public static function fetchAll($result_request){
        $ret_array = array();
        while($fetch = ODBCHelper::fetch($result_request)){
            if($fetch){
                array_push($ret_array, $fetch);
            }
        }
        return $ret_array;
    }

}
?>
