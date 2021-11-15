<?php

/***
 * @author Patricio Bento Andrade
 * @copyright tricioandrde - Patrício Andrade All Rights Reserved
 * @license  Propriety
 * @since 2020
 * @class Query
 */

namespace app\model\splitesql; 

class  Query extends SGBD
{

    private static $sql;
    private static $stmt;

    private static $rows;
    private static $values;
    
    private static $objectVars;
    private static $array_keys;
    private static $array_values;
    private static $select_values;
    private static $select_data_for_query;
    private static $check_query_result = false;
    
    /**
     * @return bool
     */
    public static function is_true(){
        return self::$check_query_result;
    }


    /**
     * @return mixed
     */
    public static function getRows(){
        return self::$rows;
    }

    /**
     * @return mixed
     */
    public static function getValues(){
        return self::$values;
    }

    /**
     * @param array $rows
     */
    public static function setRows( $rows): void {
        self::setObjectToGetArray($rows);
        self::$rows = str_replace(['[',']', '{','}', '"' ,'"'],  '', self::encode_json(array_keys(self::getObjectConvertedToArray())));
    }

    /**
     * @param array $values
     */
    public static function setValues( $values): void {
        self::setObjectToGetArray($values);
        self::$values = str_replace('"', '\'' , str_replace(['[',']', '{','}'],  '', self::encode_json(array_values(self::getObjectConvertedToArray()))));
    }
    
    /**
     * @method setObjectToGetArray
     * @param values 
     * Convert object to array
     */

    public static function setObjectToGetArray($values){
        if (is_object($values)) $values = get_object_vars($values);
        self::$objectVars = $values;
    }

    /**
     * @return converted array
     */
    public static function getObjectConvertedToArray(){
        return self::$objectVars;
    }


    /**
     * @method setArrayToGetKeys
     * @param array to get Keys 
     */

    public static function setArrayToGetKeys(array $array){
        self::$array_keys = array_keys($array);
    }
    
    /**
     * @method
     * @param array $values  
     * @return array_keys
     */
    public function getArrayKeys(){
        return self::$array_keys;
    }


    /**
     * @method setArrayToGetValues
     * @param array to get Values
     */

    public static function setArrayToGetValues(array $array){
        self::$array_values = array_values($array);
    }
    
    /**
     * @method
     * @param array $values  
     * @return array_values
     */
    private static function getArrayValues(){
        return self::$array_values;
    }

    public static function setSelectValues($values){
        self::$select_values = str_replace('$', '\'' , str_replace(['[',']', '\'' ,'{','}'],  '', self::encode_json(array_values($values))));
    }

    public static function getSelectValues(): string
    {
        return self::$select_values;   
    }

    /**
     * @method
     * @param $select values
     */
    public static function setSelectQueryValues($values){
            self::setObjectToGetArray($values);

            $_data = self::getObjectConvertedToArray();
            self::setArrayToGetKeys($_data);
            self::setArrayToGetValues($_data);
            $_keys = self::getArrayKeys();
            $_values = self::getArrayValues();
            
            $new = [];
            for($i = 0; $i < count($_keys); $i++):
                $new[$i] = $_keys[$i] . "=\$" . $_values[$i] ."\$";
            endfor;

            self::setSelectValues($new);
            self::$select_data_for_query =  self::getSelectValues(); 
    }

    /**
     * @param 
     * @return string
     */
    private static function getSelectQueryValues():string {
        return self::$select_data_for_query;
    }

    /**
     * @param bool $state
     */
    private static function setQueryResult(bool $state){
        self::$check_query_result = $state;
    }

    /*
     * @encode array to Json
     * @return json array
     */
    public static function encode_json(array $array){
        $array = str_replace('\\',' ',json_encode($array, JSON_UNESCAPED_UNICODE));
        $array = str_replace('  ', '/', $array);
        $array = str_replace(' /', '/', $array);
        return $array;
    }

    public static function sql_insert(string $table, $param){
        self::setRows($param); self::setValues($param);
        self::$sql = consts::insert." into {$table} (".self::getRows().") values (".self::getValues().")";

        self::$stmt = Connection::connect()->prepare(self::$sql);
        if (self::$stmt->execute()):
            self::setQueryResult(true);
        endif;
    }

  
    /**
     * @Model static function
     * @param string $SQL
     * @param string|null $return
     * @return array|bool|int|null
     */

    public static function sql_query(string $SQL, string $return = null){
        self::$sql = $SQL;
        print_r($SQL);
        self::$stmt = Connection::connect()->prepare(self::$sql);
        if (self::$stmt->execute()):
            if (false !== stripos($SQL, consts::create)) self::setQueryResult(true);
            if (false !== stripos($SQL, consts::update)) self::setQueryResult(true);
            if (false !== stripos($SQL, consts::delete)) self::setQueryResult(true);
            if (false !== stripos($SQL, consts::select)):
                if ($return == consts::fetch || $return == null):
                    self::setQueryResult(true);
                    return self::$stmt->fetchAll(\PDO::FETCH_OBJ);
                elseif ($return == consts::count):
                    self::setQueryResult(true);
                    return self::$stmt->rowCount();
                endif;
            endif;
        else:
            self::setQueryResult(false);    
        endif;
    }

    /**
     * 
     * @return Query
     */
    public static function sql_select(string $row, string $table, $values, string $in_where_condictions = '', string $after_where_condictions = '')
    {
        self::setSelectQueryValues($values);
        $where = str_replace(',', " ${in_where_condictions} ", self::getSelectQueryValues());
        $where = str_replace('"', '', $where);
        return self::sql_query(consts::select." ${row} from `${table}` where ${where} ${after_where_condictions};");
    }
}