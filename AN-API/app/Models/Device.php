<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer',
        'model',
        'type',
        'r-throughput', // tento parameter je povazovany ako IPsec IMIX throughput (Mbps)
        'r-branch', // tento parameter indikuje velkost branchu (small, medium, large) podla poctu pouzivatelov
        's-forwarding_rate', // tento parameter je povazovany ako Forwarding Rate (Mpps)
        's-switching_capacity', // tento parameter je povazovany ako Switching Capacity (Gbps)
        's-vlan',
        's-L3',
        'price', // in €
    ];
    protected $table = 'devices';
    protected $primaryKey = 'device_id';

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
        return $this->hasMany(DevicesInNetwork::class, 'device_id', 'device_id');
    }

    public function ports()
    {
        return $this->hasMany(Port::class, 'device_id', 'device_id');
    }
}
