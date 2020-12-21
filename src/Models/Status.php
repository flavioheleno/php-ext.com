<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model {
  public const BUILD   = 'BUILD';
  public const SKIP    = 'SKIP';
  public const PASS    = 'PASS';
  public const FAIL    = 'FAIL';
  public const PENDING = 'PENDING';

  /**
   * Indicates if the model's ID is auto-incrementing.
   *
   * @var bool
   */
  public $incrementing = false;
  /**
   * The data type of the auto-incrementing ID.
   *
   * @var string
   */
  protected $keyType = 'string';
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
    'build_time' => 0,
    'changed'    => false
  ];
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'label'
  ];
  /**
   * The attributes that aren't mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'file',
    'log',
    'build_time',
    'changed'
  ];
  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'build_time' => 'int'
  ];

  /**
   * Set the changed attribute based on label change.
   *
   * @param  string  $value
   *
   * @return void
   */
  public function setLabelAttribute($value) {
    $value = strtoupper($value);
    $this->attributes['changed'] = ($this->attributes['label'] ?? '') !== $value;
    $this->attributes['label'] = $value;
  }
}
