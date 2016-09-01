<?php
namespace App\Models;


class Page extends Model
{
    protected $insertable = array(
        'name_az',
        'name_ru',
        'name_en',
        'text_az',
        'text_ru',
        'text_en',
        'purpose'
    );
}