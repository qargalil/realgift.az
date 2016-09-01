<?php
    namespace App\Models;

    class Image extends Model{
        protected $insertable = array(
            'product_id',
            'name'
        );
    }