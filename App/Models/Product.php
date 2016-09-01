<?php
    namespace App\Models;
    
    class Product extends Model{
        protected $insertable = array(
            'name_az',
            'name_ru',
            'name_en',
            'price',
            'text_az',
            'text_ru',
            'text_en',
            'color_az',
            'color_en,',
            'color_ru',
            'material_az',
            'material_en,',
            'material_ru',
            'tags_az',
            'tags_en,',
            'tags_ru',
            'image',
            'user_id',
            'views',
            'amount',
            'ok'
        );
    }