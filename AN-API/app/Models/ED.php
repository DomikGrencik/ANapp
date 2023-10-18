<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ED extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'FE_ports',
        'GE_ports',
        'wireless'
    ];
    protected $table = 'e_d_s';
    protected $primaryKey = 'ED_id';

    public function getCreatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function devices_in_networks()
    {
        return $this->hasMany(DevicesInNetwork::class, 'device_id', 'ED_id');
    }
}
