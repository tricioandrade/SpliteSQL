<?php

/***
 * @author Patricio Bento Andrade
 * @copyright tricioandrade - Patrício Andrade All Rights Reserved
 * @license  Propriety
 * @since 2020
 * @class Query
 */

namespace App\Models\splitesql;

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
    private static $array_to_filter;
    private static $select_data_for_query;
    private static $check_query_result = false;

    private static $Query;

    private static $queryConsts = [
        consts::create,
        consts::update,
        consts::select,
        consts::delete
    ];
    
    /**
     * @return bool
     */
    public static function is_true(){
        return self::$check_query_result;
    }

    /**
     * @param mixed $Query
     */
    private static function setQuery($Query): void {
        self::$Query = $Query;
    }

    /**
     * @return mixed
     */
    private static function getQuery(){
        return self::$Query;
    }

    /**
     * @return mixed
     */
    private static function getRows(){
        return self::$rows;
    }

    /**
     * @return mixed
     */
    private static function getValues(){
        return self::$values;
    }

    /**
     * @param array $rows
     */
    private static function setRows( $rows): void {
        self::setObjectToGetArray($rows);
        self::$rows = str_replace(['[',']', '{','}', '"' ,'"'],  '', self::encode_json(array_keys(self::getObjectConvertedToArray())));
    }

    /**
     * @param array $values
     */
    private static function setValues( $values): void {
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

    private static function setArrayToGetKeys(array $array){
        self::$array_keys = array_keys($array);
    }
    
    /**
     * @method
     * @param array $values  
     * @return array_keys
     */
    public static function getArrayKeys(){
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

    public static function setValuesAndCleanIt($values){
        self::$select_values = str_replace('$', '\'' , str_replace(['[',']', '\'' ,'{','}'],  '', self::encode_json(array_values($values))));
    }

    public static function getValuesCleaned(): string{
        return self::$select_values;   
    }

    /**
     * @method
     * @param $select values
     */
    private static function setSelectQueryValues($values){
            self::setObjectToGetArray($values);

            $_data = self::getObjectConvertedToArray();
                self::setArrayToGetKeys((array)$_data);
                self::setArrayToGetValues((array)$_data);
            $_keys = self::getArrayKeys();
            $_values = self::getArrayValues();
            
            $new = [];
            for($i = 0; $i < count($_keys); $i++):
                $new[$i] = $_keys[$i] . "=\$" . $_values[$i] ."\$";
            endfor;

            self::setValuesAndCleanIt($new);
            self::$select_data_for_query =  self::getValuesCleaned();
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
    private static function encode_json(array $array){
        $array = str_replace('\\',' ',json_encode($array, JSON_UNESCAPED_UNICODE));
        $array = str_replace('  ', '/', $array);
        $array = str_replace(' /', '/', $array);
        return $array;
    }

    /**
     * @param $table
     * @param $param
     */
    public static function sql_insert($table, $param){
        self::setRows($param); self::setValues($param);
        self::$sql = consts::insert." into {$table} (".self::getRows().") values (".self::getValues().")";
        self::$stmt = Connection::connect()->prepare(self::$sql);
        if (self::$stmt->execute()):
            self::setQueryResult(true);
        endif;
    }

    /**
     * @param mixed $array_to_filter
     */
    public static function setArrayToFilter($array_to_filter): void{
        if (is_object($array_to_filter)) $array_to_filter = self::setObjectToGetArray($array_to_filter);
        self::setArrayToGetKeys($array_to_filter);
        $array_keys = self::getArrayKeys();
        for($i=0; $i < count($array_to_filter); $i++):
            self::$array_to_filter[$array_keys[$i]] = filter_var($array_to_filter[$array_keys[$i]], FILTER_SANITIZE_STRING);
        endfor;
    }

    /**
     * @return mixed
     */
    public static function getArrayToFilter(){
        return self::$array_to_filter;
    }

    /**
     * @Model static function
     * @param string $SQL
     * @param string|null $type_of_return
     * @return array|bool|int|null
     */

    public static function sql_query(string $SQL, string $type_of_return = null){
        self::$sql = $SQL;
        self::$stmt = Connection::connect()->prepare(self::$sql);
        if (self::$stmt->execute()):
            for ($i = 0; $i < count(self::$queryConsts); $i++)
                false !== stripos($SQL, self::$queryConsts[$i] ) ? self::setQueryResult(true) : self::setQueryResult(false);
            if (false !== stripos($SQL, consts::select)):
                if ($type_of_return == consts::fetch || $type_of_return == null):
                    self::setQueryResult(true);
                    self::setQuery(self::$stmt->fetchAll(\PDO::FETCH_OBJ));
                elseif ($type_of_return == consts::count):
                    self::setQueryResult(true);
                    self::setQuery(self::$stmt->rowCount());
                endif;
            endif;
        else:
            self::setQueryResult(false);    
        endif;

        return self::getQuery();
    }

    /**
     * 
     * @return array|bool|int
     */
    public static function sql_select(string $rows_to_select, string $table, $values, string $in_where_condictions = '', string $after_where_condictions = '')
    {
        self::setSelectQueryValues($values);
        $where = str_replace(',', " ${in_where_condictions} ", self::getSelectQueryValues());
        $where = str_replace('"', '', $where);
        return self::sql_query(consts::select." ${$rows_to_select} from `${table}` where ${where} ${after_where_condictions};");
    }
}