<?php

namespace Scoop\Database\Query;

use Scoop\Database\Model;
use Scoop\Database\Model\Generic;
use Scoop\EventPool;

/**
 * Class Buffer
 * @package Scoop\Database\Query
 */
class Buffer {

    /**
     * @var
     */
    private $columnNames;

    /**
     * @var EventPool
     */
    private $events;

    /**
     * @var bool
     */
    private $insertIgnore;

    /**
     * @var string
     */
    private $insertValuesSql;

    /**
     * @var int
     */
    private $maxSize;

    /**
     * @var \Scoop\Database\Model[]
     */
    private $models;

    /**
     * @var string
     */
    private $modelClass;

    /**
     * @var string[]
     */
    private $queryParams;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $table;

    /**
     * Buffer constructor.
     *
     * @param $maxSize
     * @param $modelClass
     *
     * @throws \Exception
     */
    public function __construct ( $maxSize, $modelClass ) {

        $this->events = new EventPool();

        $model = new $modelClass();

        if( !is_a( $model, \Scoop\Database\Model::class ) ) {
            throw new \Exception( "model class must implement 'Scoop\\Database\\Model'" );
        }

        $this->columnNames = '';
        foreach ( $model->get_column_names() as &$columnName ) {

            // don't save id columns
            if ( $model::AUTO_INCREMENT_COLUMN === $columnName ) {
                continue;
            }

            $this->columnNames .= "`{$columnName}`,";
        }
        $this->columnNames = rtrim($this->columnNames, ',');

        $this->maxSize = $maxSize;
        $this->modelClass = get_class( $model );
        $this->table = $model->get_sql_table_name();

        $this->reset();
    }

    /**
     * Buffers inserts to the db
     * @param \Scoop\Database\Model $model
     *
     * @return bool
     */
    public function insert ( Model $model ) {

        // don't insert if the maxSize is less than 1
        // don't insert something that isn't the model we are expecting
        if( $this->maxSize <= 0 || $this->modelClass !== get_class( $model ) ) {
            return false;
        }


        list($_, $values, $queryParams, $_) = $model->get_sql_insert_values();

        // add query params
        $this->queryParams = array_merge( $this->queryParams, $queryParams );

        // increment size of buffer
        $this->size++;

        // append sql string
        $this->insertValuesSql .= "({$values}),";

        // add model to our array of models
        $this->models[] = $model;

        // flush if buffer is too large
        if( $this->size >= $this->maxSize ) {
            $this->flush();
        }

        return true;

    }

    /**
     * Flushes the buffer to the db
     *
     * @throws \Exception
     */
    public function flush () {

        if( $this->size === 0 ) {
            return false;
        }

        //  remove trailing commas from built sql values
        $this->insertValuesSql = rtrim( $this->insertValuesSql, ',' );

        // build insert statement
        $sql =
            "INSERT " . ($this->insertIgnore ? 'IGNORE ' : '') . "INTO
              {$this->table}(
                {$this->columnNames}
              )
              VALUES
              {$this->insertValuesSql}
            ";

        // do the insert
        Generic::query( $sql, $this->queryParams );

        // update models with their autoincrement ids
        $insertId = Generic::$connection->get_insert_id();
        if ( $insertId ) {
            foreach ( $this->models as &$model ) {
                $model->__set( $model::AUTO_INCREMENT_COLUMN, $insertId++ );
                $model->set_loaded_from_database(true);
            }
        }

        $this->reset();

        $this->events->trigger('flush', $this);

        return true;
    }

    /**
     * @return bool
     */
    public function get_insert_ignore () {

        return $this->insertIgnore;
    }

    /**
     * @return \Scoop\Database\Model[]
     */
    public function get_models () {

        return $this->models;
    }

    /**
     * @param callable $callback
     */
    public function on_flush( callable $callback ) {

        $this->events->on('flush', $callback);
    }

    /**
     * @param bool $insertIgnore
     */
    public function set_insert_ignore ( $insertIgnore = true ) {

        $this->insertIgnore = (bool)$insertIgnore;
    }

    /**
     * Destructively clears the buffer's content
     */
    private function reset () {

        $this->queryParams = [ ];
        $this->size = 0;
        $this->insertValuesSql = '';
        $this->models = [ ];
    }
}
