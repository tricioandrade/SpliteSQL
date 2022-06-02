<?php


namespace Tricioandrade\Splitesql;

abstract class Attributes
{
    const fetch     = 'fetch';
    const create    = 'CREATE';
    const update    = 'UPDATE';
    const delete    = 'DELETE';
    const select    = 'select';
    const insert    = 'insert';
    const where     = 'WHERE';
    const set       = 'SET';
    const limit     = 'LIMIT';

    public static $symbol = '=';
    public static $limitRows;
    public static $row;
    public static $or_;
    public static $and_;
    public static $orderBy;

    protected $create              = 'CREATE';
    protected $update              = 'UPDATE';
    protected $delete              = 'DELETE';
    protected $select              = 'SELECT';
    protected $insert              = 'INSERT';

//    protected $columns             = 'COLUMNS';

//    protected $columnType          = 'COLUMN_TYPE';
//    protected $columnName          = 'COLUMN_NAME';
//    protected $columnComment       = 'COLUMN_COMMENT';
//    protected $columnKey           = 'COLUMN_KEY';
//    protected $columnDefault       = 'COLUMN_DEFAULT';
//
//    protected $dataType            = 'DATA_TYPE';
//
//    protected $informationSchema   = 'INFORMATION_SCHEMA';
//
//    protected $tableSchema         = 'TABLE_SCHEMA';
//    protected $tableName           = 'TABLE_NAME';
//    protected $characterMLength    = 'CHARACTER_MAXIMUM_LENGTH';
//
//    protected $where               = 'WHERE';
//    protected $set                 = 'SET';
//    protected $limit               = 'LIMIT';
//    protected $alter               = 'ALTER';
//    protected $change              = 'CHANGE';
//    protected $join                = 'JOIN';
//
//    protected $constraint          = 'CONSTRAINT';
//    protected $references          = 'REFERENCES';

    protected static $and          = 'AND';
    protected static $or           = 'OR';

    protected static $order           = 'ORDER BY';

}