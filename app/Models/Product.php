<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    protected $fillable = [
        'code', 'name',
        'quantity',
        'image_path',
        'price', 
        'entry_date',
        'expiration_date',
        'status'
    ];
    
    protected $dates = ['entry_date', 'expiration_date'];
}
