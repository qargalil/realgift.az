<?php

    namespace App\Models;

    class Category extends Model{
        protected $insertable=array(
            "name_az",
            "name_en",
            "name_ru",
            "category_id",
            "main"
        );

    }