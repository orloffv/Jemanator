<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Jemanator_Core {

    public function create_table($model)
    {
        $fields = Jelly::meta($model)->fields();

        $table = new Kohana_Jemanator_Statement_CreateTable(Jelly::meta($model)->table(),
            array('id' => false, 'created' => false, 'modified'    => false, 'primary_key' => false)
        );

        foreach ($fields as $field)
        {
            if ($field->in_db)
            {
                $table->addColumn($this->get_type($field), $field->column, array('null' => $this->get_null($field), 'default' => $this->get_default($field)));

                if ($field->primary)
                {
                    $table->primaryKey($field->column);
                }

                if ($field->unique)
                {
                    $table->addKey(array($field->column => array()), array('name' => $field->column.'_unique','unique'));
                }
            }
        }

        return $table->toSQL();
    }

    protected function get_type($field)
    {
        if ($field instanceof Jelly_Field_Primary)
        {
            $type = 'primary';
        }
        elseif ($field instanceof Jelly_Field_Integer)
        {
            $type = 'integer';
        }
        elseif ($field instanceof Jelly_Field_Boolean)
        {
            $type = 'bool';
        }
        elseif ($field instanceof Jelly_Field_Text)
        {
            $type = 'text';
        }
        elseif ($field instanceof Jelly_Field_String)
        {
            $type = 'string';
        }
        elseif ($field instanceof Jelly_Field_My_Datetime)
        {
            $type = 'datetime';
        }
        else
        {
            var_dump($field);
        }

        return $type;
    }

    protected function get_null($field)
    {
        //var_dump($field->allow_null);
        foreach ($field->rules as $rule)
        {
            if (Arr::get($rule, 0) == 'not_empty')
            {
                return false;
            }
        }

        return true;
    }

    protected function get_default($field)
    {
        if ($field instanceof Jelly_Field_My_Datetime)
        {
            return null;
        }
        else
        {
            return $field->default;
        }
    }
}
