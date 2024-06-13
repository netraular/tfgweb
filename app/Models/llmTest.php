<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class llmTest extends Model
{
    use HasFactory;
    protected $table='LlmTest';
    public $timestamps = false;

    public function answers()
    {
        return $this->hasMany(LlmTestAnswers::class, 'question_id', 'id');
    }
}
