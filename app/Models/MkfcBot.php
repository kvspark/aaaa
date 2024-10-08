<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkfcBot extends Model
{
    use HasFactory;

    public function referrals()
    {
        return $this->hasMany(MkfcBot::class, 'referred_by', 'id');
    }

    protected $fillable = [
        'telegram_id',
        'telegram_username',
        'earns',
        'my_referral_code',
        'referred_by',            
    ];
}
