<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedEvent extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'event_id',
    ];
    
    /**
     * Get the user that saved the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the event that was saved.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
