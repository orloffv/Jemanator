<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Jemanator_Table {

    protected $name;
    protected $fields = array();
    protected $engine = 'InnoDB';
    protected $charset = 'utf8';
    protected $indexes = array();
    protected $keys = array();
    protected $primary_key = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function set_field(Kohana_Jemanator_Field $field)
    {
        $this->fields[] = $field;
    }

    public function set_indexes(Kohana_Jemanator_Index $index)
    {
        $this->indexes[] = $index;
    }

    public function set_key(Kohana_Jemanator_Key $key)
    {
        $this->keys[$key->getName()] = $key;
    }

    public function set_primary_key($key)
    {
        $this->primary_key = $key;
    }

    public function toSQL()
    {
        $sql = "CREATE TABLE `{$this->name}` (\n\t";

        $rows = array();

        foreach( $this->fields as $field ) {
            $rows[] = $field->toSQL();
        }

        // Insert the PK
        if( ! is_null( $this->primary_key ) ) {
            $pk = ( is_array( $this->primary_key ) ) ? implode( '`, `', $this->primary_key ) : $this->primary_key;
            $rows[] = "PRIMARY KEY( `$pk` )";
        }

        // Generate any other indexes/keys here
        foreach( $this->keys as $name => $key ) {
            $rows[] = $key->toSQL();
        }
        foreach( $this->indexes as $name => $index ) {
            $rows[] = $index->toSQL();
        }

        $sql .= implode( ",\n\t", $rows );

        $sql .= "\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset};\n\n";

        return $sql;
    }

}