<?php

	class Kohana_Jemanator_Statement_CreateTable extends Kohana_Jemanator_Statement {

		protected $_tableName;
		protected $_engine;
		protected $_charset;

		protected $_columns = array();
		protected $_indexes = array();
		protected $_keys = array();
		protected $_primaryKey = null;

		/*!
			Default options for creating the table.
			
			\param id  If string, it creates a integer, auto-increment column. If false, it does not.
			\param created If string, create a timestamp created column. If false, it does not.
			\param modified If string, it creates a timestamp modified column. If false, it does not.
			\param primary_key If string or array, it is the column name(s) of the primary key. If true, it is the id column. If false there is no Primary Key.
			\param engine The engine to use.
			\param charset The charset to use.
		*/
		protected $_default_options = array(
			'id'          => 'id',
			'created'     => 'created',
			'modified'    => 'modified',
			'primary_key' => true,
			'engine'      => 'InnoDB',
			'charset'     => 'utf8',
		);

		/*!
			Create a new table.

			\param name The name of the table.
			\param args An optional array of options for the table.

			\sa $_default_options
		*/
		public function __construct ( $name, $args = null ) {
			$this->_tableName = $name;

			if( is_array( $args ) ) { $args = array_merge( $this->_default_options, $args ); }
			else { $args = $this->_default_options; }

			if( false !== $args['id'] ) {
				$this->addColumn( 'integer', $args['id'], array( 'size' => 11, 'null' => false, 'unsigned' => true, 'auto_increment' => true ) );
				if( true === $args['primary_key'] ) {
					$this->primaryKey( $args['id'] );
				}
			}

			if( false !== $args['created'] ) {
				$this->addColumn( 'integer', $args['created'], array( 'null' => false, 'unsigned' => true ) );
			}

			if( false !== $args['modified'] ) {
				$this->addColumn( 'integer', $args['modified'], array( 'null' => false ) );
			}

			if( is_string( $args['primary_key'] ) or is_array( $args['primary_key'] ) ) {
				$this->primaryKey( $args['primary_key'] );
			}

			$this->engine( $args['engine'] );
			$this->charset( $args['charset'] );
		}

		public function toSQL () {
			$sql = "CREATE TABLE `{$this->_tableName}` (\n\t";

			$rows = array();

			foreach( $this->_columns as $column ) {
				$rows[] = $column->toSQL();
			}

			// Insert the PK
			if( ! is_null( $this->_primaryKey ) ) {
				$pk = ( is_array( $this->_primaryKey ) ) ? implode( '`, `', $this->_primaryKey ) : $this->_primaryKey; 
				$rows[] = "PRIMARY KEY( `$pk` )";
			}

			// Generate any other indexes/keys here
			foreach( $this->_keys as $name => $key ) {
				$rows[] = $key->toSQL();
			}
			foreach( $this->_indexes as $name => $index ) {
				$rows[] = $index->toSQL();
			}

			$sql .= implode( ",\n\t", $rows );

			$sql .= "\n) ENGINE={$this->_engine} DEFAULT CHARSET={$this->_charset};";


			return $sql;
		}

		/*!
			Set the engine for this table.

			\param engine The string name of the engine to use.
		*/
		public function engine ( $engine ) { $this->_engine = $engine; }

		/*!
			Set the charset for this table.

			\param charset The string name of the charset to use.
		*/
		public function charset ( $charset ) { $this->_charset = $charset; }

		/*!
			Set the primary key for this table.

			\param columnName The string or array of column name(s) for the primary key.
		*/
		public function primaryKey ( $columnName ) { $this->_primaryKey = $columnName; }

		/*!
			Set the name of this table.

			\param tableName The name for the table.
		*/
		public function tableName ( $tableName ) { $this->_tableName = $tableName; }

		/*!
			Add a column to this table.

			\param type The type of column to add.
			\param name The name of the new column.
			\param traits An optional array of traits for this column.

			\sa Kohana_Migration_Column::$_traits
		*/
		public function addColumn ( $type, $name, $traits = null ) {
			$this->_columns[$name] = new Kohana_Jemanator_Column( $name, $type, $traits );
		}

		/*!
			Add an index to this table.

			\param columns An array of columns to put the inex on.
			\param traits An optional array of traits for the index.

			\sa Kohana_Migration_Index
		*/
		public function addIndex ( $columns, $traits = null ) {
			$index = new Kohana_Jemanator_Index($columns, $traits);
			$this->_indexes[$index->getName()] = $index;
		}

		/*!
			Add a key to this table.

			\param columns An array of columns to put the key on.
			\param traits An optional array of traits for the key.

			\sa Kohana_Migration_Key
		*/
		public function addKey ( $columns, $traits = null ) {
			$key = new Kohana_Jemanator_Key($columns, $traits);
			$this->_keys[$key->getName()] = $key;
		}

		/*!
			Add a foreign key to this table.

			\param near_columns An array of columns in the near table to match to foreign columns.
			\param far_table The name of the foreign table.
			\param far_columns An array with a 1:1 matching of column names on the foreign table.
			\param traits An optional array of traits to apply to this table.

			\sa Kohana_Migration_Key_Foreign::$_traits
		*/
		public function addForeignKey ( $near_columns, $far_table, $far_columns, $traits = null ) {
			$key = new Kohana_Jemanator_Key_Foreign($near_columns, $far_table, $far_columns, $traits);
			$this->_keys[$key->getName()] = $key;
		}

		public function __set ( $type, $value ) {
			if( Kohana_Jemanator_Column::isType($type) ) {
				// $t->integer = array( 'name', 'option' => $value );
				if( is_array( $value ) ) {
					$name = array_shift( $value );
					$this->addColumn( $type, $name, $value );
				}
				// $t->integer = 'name';
				else {
					$this->addColumn( $type, $value );
				}
			}
			else if ( Kohana_Jemanator_Index::isType($type) ) {
				if( is_array( $value ) ) {
					// $t->index = array( array( "column_name", "another_column" ), array( "btree" ) );
					if( 2 <= count( $value ) ) {
						$one = reset( $value );
						$two = next( $value );
						if( is_array( $one ) and is_array( $two ) ) {
							$this->addIndex( $one, array_merge( array( $type ), $two ) );
						}
						// $t->index = array( "column_name", array( "btree" ) );
						else if( is_array( $two ) ) {
							$this->addIndex( array( $one => null ), array_merge( array( $type ), $two ) );
						}
						// $t->index = array( "column_name", "another_column", "and_another" );
						else {
							$this->addIndex( $value, array( $type ) );
						}
					}
				}
				// $t->index = "column_name"
				else {
					$this->addIndex( array( $value => null), array( $type ) );
				}
			}
			else if ( Kohana_Jemanator_Key::isType($type) ) {
				if( is_array( $value ) ) {
					// $t->index = array( array( "column_name", "another_column" ), array( "btree" ) );
					if( 2 <= count( $value ) ) {
						$one = reset( $value );
						$two = next( $value );
						if( is_array( $one ) and is_array( $two ) ) {
							$this->addKey( $one, array_merge( array( $type ), $two ) );
						}
						// $t->index = array( "column_name", array( "btree" ) );
						else if( is_array( $two ) ) {
							$this->addKey( array( $one => null ), array_merge( array( $type ), $two ) );
						}
						// $t->index = array( "column_name", "another_column", "and_another" );
						else {
							$this->addKey( $value, array( $type ) );
						}
					}
				}
				// $t->index = "column_name"
				else {
					$this->addKey( array( $value => null), array( $type ) );
				}
			}
		}

	}

