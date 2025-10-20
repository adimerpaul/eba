<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class ControlProceso extends Model implements Auditable
{
    use AuditableTrait,SoftDeletes;
    protected $fillable = [
        'cosecha_id',
        'producto_id',
        'tanque_id',
        'fecha_proceso',
        'dato1',
        'dato2',
        'dato3',
        'dato4',
        'dato5',
        'dato6',
        'dato7',
        'dato8',
        'dato9',
        'dato10',
        'dato11',
        'dato12',
        'dato13',
        'dato14',
        'dato15',
        'user_id',
        'observaciones'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    function  cosecha()
    {
        return $this->belongsTo(AcopioCosecha::class, 'cosecha_id');
    }
    function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    function tanque()
    {
        return $this->belongsTo(Tanque::class, 'tanque_id');
    }
    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //            $table->unsignedBigInteger('cosecha_id');
    //            $table->foreign('cosecha_id')->references('id')->on('acopio_cosechas');
    //            $table->unsignedBigInteger('producto_id');
    //            $table->foreign('producto_id')->references('id')->on('productos');
    //            $table->unsignedBigInteger('tanque_id');
    //            $table->foreign('tanque_id')->references('id')->on('tanques');
    //            $table->timestamp('fecha_proceso')->default(DB::raw('now()'));
    //            $table->decimal('dato1', 7, 2)->nullable();
    //            $table->decimal('dato2', 7, 2)->nullable();
    //            $table->decimal('dato3', 7, 2)->nullable();
    //            $table->decimal('dato4', 7, 2)->nullable();
    //            $table->decimal('dato5', 7, 2)->nullable();
    //            $table->decimal('dato6', 7, 2)->nullable();
    //            $table->decimal('dato7', 7, 2)->nullable();
    //            $table->decimal('dato8', 7, 2)->nullable();
    //            $table->decimal('dato9', 7, 2)->nullable();
    //            $table->decimal('dato10', 7, 2)->nullable();
    //            $table->decimal('dato11', 7, 2)->nullable();
    //            $table->decimal('dato12', 7, 2)->nullable();
    //            $table->decimal('dato13', 7, 2)->nullable();
    //            $table->decimal('dato14', 7, 2)->nullable();
    //            $table->decimal('dato15', 7, 2)->nullable();
    //            $table->unsignedSmallInteger('user_id')->nullable();
    //            $table->foreign('user_id')->references('id')->on('users');
    //            $table->string('observaciones', 100)->nullable();
    //            $table->softDeletes();
    //            $table->timestamps();
}
