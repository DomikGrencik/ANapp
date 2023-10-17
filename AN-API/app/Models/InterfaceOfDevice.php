<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceOfDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'IP_address',
        'category',
        'type',
        'interface_id2',
        'device_id'
    ];
    protected $table = 'interface_of_devices';
    protected $primaryKey = 'interface_id';

    public function getCreatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('j.n.y', strtotime($value));
    }
}
