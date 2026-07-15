<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function options(): HasMany
    {
        return $this->hasMany(EventOption::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function loadAllRelations(): static
    {
        return $this->load([
            'options' => function (HasMany $query) {
                $query->withCount([
                    'responses as yes_count' => fn (Builder $query) => $query->where('answer', Answer::YES->value),
                    'responses as not_sure_count' => fn (Builder $query) => $query->where('answer', Answer::NOT_SURE->value),
                    'responses as no_count' => fn (Builder $query) => $query->where('answer', Answer::NO->value),
                ]);
            },
            'participants.responses',
        ]);
    }
}
