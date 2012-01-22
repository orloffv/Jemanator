<?php defined('SYSPATH') or die('No direct script access.');

Route::set('jemanator', 'jemanator(/<action>(/<id>))',
    array(
        'action' => '(index|create)',
        'id' => '[A-Za-z]+',
    ))->defaults(array(
        'controller'=> 'jemanator',
        'action' => 'index',
    ));
