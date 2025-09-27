<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeminiConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'endpoint_url',
        'description',
        'is_active',
        'is_default',
        'temperature',
        'max_tokens',
        'max_output_tokens',
        'safety_settings',
        'generation_config',
        'priority',
        'supports_vision',
        'supports_video',
        'supports_audio',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'temperature' => 'decimal:2',
        'safety_settings' => 'array',
        'generation_config' => 'array',
        'supports_vision' => 'boolean',
        'supports_video' => 'boolean',
        'supports_audio' => 'boolean',
    ];

    /**
     * Scope for active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default configuration
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for vision-capable models
     */
    public function scopeVisionCapable($query)
    {
        return $query->where('supports_vision', true);
    }

    /**
     * Get the default Gemini configuration
     */
    public static function getDefault()
    {
        return static::default()->active()->first() 
            ?? static::active()->orderBy('priority', 'desc')->first();
    }

    /**
     * Get active configurations ordered by priority
     */
    public static function getActiveConfigs()
    {
        return static::active()->orderBy('priority', 'desc')->get();
    }

    /**
     * Get generation config with defaults
     */
    public function getGenerationConfigAttribute($value)
    {
        $config = json_decode($value, true) ?? [];
        
        return array_merge([
            'temperature' => $this->temperature,
            'maxOutputTokens' => $this->max_output_tokens,
            'topP' => 0.95,
            'topK' => 64,
        ], $config);
    }

    /**
     * Get safety settings with defaults
     */
    public function getSafetySettingsAttribute($value)
    {
        $settings = json_decode($value, true) ?? [];
        
        return array_merge([
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
        ], $settings);
    }
}
