<?php
namespace App\Models;

class Comment extends Model{
    protected $insertable = array(
        'user_name',
        'comment',
        'product_id',
        'approved',
        'time'
    );
}