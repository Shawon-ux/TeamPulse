<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model { protected $fillable = ['team_id','number','plan','amount','status']; }
