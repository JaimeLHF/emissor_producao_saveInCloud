<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acabamentos extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    public static function rules()
    {
        return [
            'nome' => 'required|string|max:50',
        ];
    }
}
