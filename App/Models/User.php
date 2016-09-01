<?php
    namespace App\Models;
    
    class User extends Model
    {
        protected $insertable=array(
            'email',
            'name',
            'password',
            'mobile',
            'address',
            'username',
            'is_real',
            'gender',
            'surname'
        );
    }
    