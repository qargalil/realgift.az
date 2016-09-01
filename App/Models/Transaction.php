<?php
/**
 * Created by PhpStorm.
 * User: Murad
 * Date: 6/10/2016
 * Time: 11:12 PM
 */

namespace App\Models;


class Transaction extends Model
{
    protected $insertable=array(
        'user_id',
        'payment_id',
        'amount',
        'ok',
        'money'
    );
}