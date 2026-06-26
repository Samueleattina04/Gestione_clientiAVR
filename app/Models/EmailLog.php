<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmailLog extends Model {
    protected $fillable = ['subscription_id','recipient_email','recipient_type','email_type','success','error_message'];
}
