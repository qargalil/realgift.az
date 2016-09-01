<?php

namespace App\Models;

class Wishlist extends Model{
    protected $insertable = array(
        'id',
        'user_id',
        'product_id'
    );
}