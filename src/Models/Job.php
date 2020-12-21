<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model {
  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  public $timestamps = true;
  /**
   * The storage format of the model's date columns.
   *
   * @var string
   */
  protected $dateFormat = 'U';
  /**
   * The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    'assigned' => false,
    'finished' => false,
    'failed'   => false
  ];
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'function',
    'payload'
  ];
  /**
   * The attributes that aren't mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'assigned',
    'finished',
    'failed'
  ];
  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'payload' => 'array'
  ];

  /**
   * Scope a query to only include successful jobs.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeSuccessful($query) {
    return $query
      ->where('finished', true)
      ->where('failed', false);
  }

  /**
   * Scope a query to only include failed jobs.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeFailed($query) {
    return $query
      ->where('finished', true)
      ->where('failed', true);
  }

  public function oldestAvailable($query) {
    return $query
      ->where('assigned', false)
      ->where('finished', false)
      ->orderByDesc('created_at')
      ->first();
  }
}
