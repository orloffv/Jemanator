<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Jemanator_Field {

    protected $name;
    protected $type;

    protected $traits;
    protected $choices;

    protected $size;
    protected $default;
    protected $null;
    protected $comment;

    //:float, :decimal, :time, :date, :binary, :boolean.

    protected static $types = array(
        'string' => array(
            'type' => 'VARCHAR(%d)',
            'size' => 255,
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
        'primary' => array(
            'type' => 'INTEGER(%d)',
            'size' => 10,
            'null' => false,
            'default' => null,
            'comment' => null,
            'traits' => array(
                'unsigned'       => 'UNSIGNED',
                'auto_increment' => 'AUTO_INCREMENT',
            ),
            'default_traits' => array('auto_increment' => true, 'UNSIGNED' => true)
        ),
        'integer' => array(
            'type' => 'INTEGER(%d)',
            'size' => 10,
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(
                'unsigned'       => 'UNSIGNED',
                'auto_increment' => 'AUTO_INCREMENT',
            ),
            'default_traits' => array('UNSIGNED' => true)
        ),
        'enum' => array(
            'type' => 'ENUM',
            'size' => null,
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(
                'unsigned'       => 'UNSIGNED',
                'auto_increment' => 'AUTO_INCREMENT',
            ),
            'choices' => array(),
            'default_traits' => array()
        ),
        // TODO: http://www.ispirer.com/doc/sqlways39/Output/SQLWays-1-211.html
        'text' => array(
            'type' => 'TEXT',
            'size' => null,
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
        'blob' => array(
            'type' => 'TEXT',
            'size' => null,
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
        'datetime' => array(
            'type' => 'DATETIME',
            'size' => null,
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
        'timestamp' => array(
            'type' => 'INTEGER(10)', // Why not TIMESTAMP? Because Kohana defaults to INT(10)
            'size' => null,
            'null' => false,
            'default' => '0',
            'comment' => null,
            'traits' => array(
                'unsigned' => 'UNSIGNED',
            ),
            'default_traits' => array(
                'unsigned' => true
            )
        ),
        'decimal' => array(
            'type' => 'DECIMAL(%d,%d)',
            'size' => array( 5, 2 ),
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
        'float' => array(
            'type' => 'FLOAT(%d,%d)',
            'size' => array( 5, 2 ),
            'null' => true,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
        'bool' => array(
            'type' => 'TINYINT',
            'size' => null,
            'null' => false,
            'default' => null,
            'comment' => null,
            'traits' => array(),
            'default_traits' => array()
        ),
    );

    public static function isType ( $name )
    {
        return array_key_exists( $name, self::$types );
    }

    public function get_type($field)
    {
        if ($field instanceof Jelly_Field_Integer)
        {
            $type = 'integer';
        }
        elseif ($field instanceof Jelly_Field_Boolean)
        {
            $type = 'bool';
        }
        elseif ($field instanceof Jelly_Field_String)
        {
            $type = 'string';
        }
        elseif ($field instanceof Jelly_Field_Text)
        {
            $type = 'text';
        }
        elseif ($field instanceof Jelly_Field_Primary)
        {
            $type = 'primary';
        }

        return $type;
    }

    public function __construct($field)
    {
        $this->name    = $field->column;
        $this->type    = $this->get_type($field);
        $this->size    = self::$types[$this->type]['size'];
        $this->null    = self::$types[$this->type]['null'];
        $this->default = self::$types[$this->type]['default'];
        $this->comment = self::$types[$this->type]['comment'];

        $this->choices  = array();
        $this->traits  = array();

        $this->default = $field->default;
    }

    public function toSQL () {

        $chunks = array(
            "`{$this->name}`",
            vsprintf( self::$types[$this->type]['type'], $this->size )
        );

        // ENUM
        $chunks_choice = array();
            if( count($this->choices) > 0 ) {
                foreach($this->choices['choices'] as $key => $value) {
                        $chunks_choice[] = "'".$value."'";
                    }
                    $chunks[] = '(' . implode( ',', $chunks_choice ) . ')';
            }

        $requested_traits = array_merge( self::$types[$this->type]['default_traits'], $this->traits );
        foreach( $requested_traits as $key => $trait ) {
            if( ( $trait === true && array_key_exists( $key, self::$types[$this->type]['traits'] ) ) ) {
                $chunks[] = self::$types[$this->type]['traits'][$key];
            }
        }

        if( ! $this->null ) { $chunks[] = 'NOT NULL'; }
        if( ! is_null( $this->default ) ) { $chunks[] = "DEFAULT '{$this->default}'"; } //! TODO: Escaping here?
        if( ! is_null( $this->comment ) ) { $chunks[] = "COMMENT '{$this->comment}'"; } //! TODO: Escaping here?

        return implode( ' ', $chunks );
    }

}

