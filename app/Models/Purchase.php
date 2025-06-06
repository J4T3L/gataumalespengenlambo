<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    

    protected $fillable = ['nama', 'alamat', 'telepon', 'pembayaran', 'total'];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
