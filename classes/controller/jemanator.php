<?php defined('SYSPATH') or die('No direct access allowed.');

class Controller_Jemanator extends Controller {

    public function before ()
    {
        parent::before();
        if( ! Kohana::$is_cli ) { throw new Kohana_Exception( "CLI Access Only" ); }
        $this->message("=============[ Jemanator ]============");
    }

    public function after () {
        parent::after();
        $this->message("=======================================");
    }

    public function action_index()
    {
        var_dump('123');
    }

    public function action_create()
    {
        $id = $this->request->param('id');

        if ($id)
        {
            $core = new Kohana_Jemanator_Core();

            $this->message($core->create_table($id));
        }
        else
        {
            return $this->action_model_list();
        }
    }

    public function action_model_list()
    {

    }

    protected function message($message)
    {
        print "\n{$message}\n";
    }

}