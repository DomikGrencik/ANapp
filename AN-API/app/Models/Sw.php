<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sw extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer',
        'type',
        'FE_ports',
        'GE_ports'
    ];
    protected $table = 'sws';
    protected $primaryKey = 'switch_id';

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
        return $this->hasMany(DevicesInNetwork::class, 'device_id', 'switch_id');
    }
}
