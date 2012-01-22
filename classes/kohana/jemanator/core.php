<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Jemanator_Core {

    public static function get_models()
    {
        $fields = Jelly::meta('page')->fields();

        $table = new Kohana_Jemanator_Table(Jelly::meta('page')->table());
        var_dump($fields);
        foreach ($fields as $field)
        {
            if ($field->in_db)
            {
                $table->set_field(new Kohana_Jemanator_Field($field));

                if ($field->primary)
                {
                    $table->set_primary_key($field->column);
                }

                if ($field->unique)
                {
                    $table->set_key(new Kohana_Jemanator_Key(array($field->column => array()), array('unique')));
                }
            }
        }

        var_dump($table->toSQL());
    }

}
