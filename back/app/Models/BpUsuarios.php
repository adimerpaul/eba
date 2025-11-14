<?php

namespace App\Models;

// OJO: ya no usamos Model aquí
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class BpUsuarios extends Authenticatable
{
    use SoftDeletes, HasApiTokens, Notifiable;

    protected $table = 'public._bp_usuarios';
    protected $primaryKey = 'usr_id';
    public $timestamps = false;

    protected $fillable = [
        'usr_prs_id',
        'usr_usuario',
        'password',
        'usr_controlar_ip',
        'sr_ga_id',
        'usr_corr_id',
        'usr_archivo',
        'usr_registrado',
        'usr_modificado',
        'usr_usr_id',
        'usr_estado',
        'remember_token',
        'usr_access_sistem',
        'deleted_at',
        'usr_niv_id',
        'usr_cargo_add',
        'usr_scar_id',
        'usr_new_password',
        'usr_zona_id',
        'usr_id_turno',
        'usr_externo_id',
        'usr_tipo_persona'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'usr_access_sistem' => 'array',
    ];
}
