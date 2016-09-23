<?php

namespace Scoop\Database;

use Scoop\Database\Model\Generic;

/**
 * Class Rows
 * @package Scoop\Database
 */
class Rows implements \Iterator, \ArrayAccess, \JsonSerializable {

    /**
     * @var int
     */
    private $numRows = 0;

    /**
     * @var $rowsStorageArray Generic[]
     */
    private $rowsStorageArray = [ ];

    /**
     * @return string
     */
    public function __toString () {

        return var_export ( $this->rowsStorageArray, true );
    }

    /**
     * @param $row Generic
     */
    public function add_row ( Generic $row ) {

        $this->rowsStorageArray[] = $row;
        ++$this->numRows;
    }

    /**
     * @return Generic
     */
    public function first () {

        return $this->get(0);
    }

    /**
     * @return null|Generic
     */
    public function last () {

        end($this->rowsStorageArray);

        return $this->get(key($this->rowsStorageArray));
    }


    /**
     * @param int $index
     *
     * @return Generic|null
     */
    public function get ( $index ) {

        if( !array_key_exists( $index, $this->rowsStorageArray ) ) {
            return null;
        }

        return $this->rowsStorageArray[$index];
    }

    /**
     * @return array Model[]
     */
    public function get_rows () {

        return $this->rowsStorageArray;
    }

    /**
     * @return int
     */
    public function get_num_rows () {

        return $this->numRows;
    }

    /**
     * @return bool
     */
    public function is_last_row () {

        $keys = array_keys($this->rowsStorageArray);
        return $this->key () === end($keys);
    }

    /**
     * @return array
     */
    public function to_array () {

        $array = [ ];

        foreach ( $this as $row ) {
            $array[] = $row->to_array ();
        }

        return $array;
    }


    /**********************************
     * Iterator functions
     **********************************/
    /**
     * get Model at current index
     * @return Model
     */
    public function current () {

        return current($this->rowsStorageArray);
    }
    /**
     * get the current position
     * @return int
     */
    public function key () {

        return key($this->rowsStorageArray);
    }

    /**
     * go to next item in array
     */
    public function next () {

        next($this->rowsStorageArray);
    }

    /**
     * Set position to beginning
     */
    public function rewind () {

        reset($this->rowsStorageArray);
    }

    /**
     * @return bool
     */
    public function valid () {

        // this isn't safe usually, but we are only storing objects in here
        return $this->current() !== false;
    }

    /**********************************
     *  ArrayAccess functions
     **********************************/
    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists ( $offset ) {

        return isset( $this->rowsStorageArray[$offset] );
    }

    /**
     * @param mixed $offset
     *
     * @return null|Model
     */
    public function offsetGet ( $offset ) {

        return isset( $this->rowsStorageArray[$offset] ) ? $this->rowsStorageArray[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet ( $offset, $value ) {

        // only allow our models in here
        if ( !is_a($value, Generic::class) ) {
            return;
        }

        // no offset? just append
        if ( is_null ( $offset ) ) {
            $this->rowsStorageArray[] = $value;
            ++$this->numRows;
        } else {
            if ( !array_key_exists( $offset, $this->rowsStorageArray )) {
                ++$this->numRows;
            }
            $this->rowsStorageArray[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset ( $offset ) {

        if ( array_key_exists( $offset, $this->rowsStorageArray ) ) {
            --$this->numRows;
        }

        unset( $this->rowsStorageArray[$offset] );
    }

    /**********************************
     * JSONSerialize functions
     **********************************/
    /**
     * @return array|Model[]
     */
    public function jsonSerialize () {

        return $this->rowsStorageArray;
    }

}
