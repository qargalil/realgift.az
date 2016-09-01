<?php
    namespace App\Models;

    class Order extends Model{
        protected $insertable = array(
            'product_id',
            'user_id',
            'amount',
            'ordered',
            'time',
            'payment_id',
            'paid'
        );
    }