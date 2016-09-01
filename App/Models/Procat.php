<?php

    namespace App\Models;

    class Procat extends Model{
        protected $insertable=array(
            'category_id',
            'product_id'
        );
    }