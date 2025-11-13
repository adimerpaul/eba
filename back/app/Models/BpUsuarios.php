<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class BpUsuarios extends Model
{
    use SoftDeletes,HasApiTokens;
    //-- public._bp_usuarios definition
    //
    //-- Drop table
    //
    //-- DROP TABLE public._bp_usuarios;
    //
    //CREATE TABLE public._bp_usuarios (
    //	usr_id serial4 NOT NULL,
    //	usr_prs_id int4 DEFAULT 1 NOT NULL,
    //	usr_usuario text NOT NULL,
    //	"password" text NOT NULL,
    //	usr_controlar_ip text NULL,
    //	sr_ga_id int4 NULL,
    //	usr_corr_id int4 NULL,
    //	usr_archivo bool NULL,
    //	usr_registrado timestamp DEFAULT now() NOT NULL,
    //	usr_modificado timestamp DEFAULT now() NOT NULL,
    //	usr_usr_id int4 NOT NULL,
    //	usr_estado bpchar(1) DEFAULT 'A'::bpchar NOT NULL,
    //	remember_token text NULL,
    //	usr_access_sistem jsonb NULL,
    //	deleted_at timestamp NULL,
    //	usr_niv_id int4 NULL,
    //	usr_cargo_add bool NULL,
    //	usr_scar_id int4 NULL,
    //	usr_new_password text NULL,
    //	usr_zona_id int4 NULL,
    //	usr_id_turno int4 NULL,
    //	usr_externo_id int4 NULL,
    //	usr_tipo_persona int4 NULL,
    //	CONSTRAINT _bp_usuarios_pkey PRIMARY KEY (usr_id)
    //);
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
    protected $casts = [
        'usr_access_sistem' => 'array', // ← IMPORTANTE para manejar JSONB como array
    ];
}
