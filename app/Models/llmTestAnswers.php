<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class llmTestAnswers extends Model
{
    use HasFactory;
    protected $table='LlmTestAnswers';
    public $timestamps = false;
}
