<?php

	class Kohana_Jemanator_Statement_DropTable extends Kohana_Jemanator_Statement {

		protected $_tableName;

		public function __construct( $tableName ) {
			$this->_tableName = $tableName;
		}

		public function toSQL () {
			return "DROP TABLE `{$this->_tableName}`;\n";
		}

	}
