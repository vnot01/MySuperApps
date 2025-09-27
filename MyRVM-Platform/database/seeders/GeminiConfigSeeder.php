<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeminiConfig;

class GeminiConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing configurations
        GeminiConfig::truncate();

        // Gemini 2.0 Flash (Default)
        GeminiConfig::create([
            'name' => 'gemini-2.0-flash',
            'display_name' => 'Gemini 2.0 Flash',
            'endpoint_url' => env('GEMINI_API_ENDPOINT_2_0_FLASH', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent'),
            'description' => 'Latest Gemini 2.0 Flash model with enhanced vision capabilities',
            'is_active' => true,
            'is_default' => true,
            'temperature' => 0.1,
            'max_tokens' => 4096,
            'max_output_tokens' => 8192,
            'priority' => 100,
            'supports_vision' => true,
            'supports_video' => false,
            'supports_audio' => false,
            'safety_settings' => [
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
            ],
            'generation_config' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 8192,
                'topP' => 0.95,
                'topK' => 64,
            ]
        ]);

        // Gemini 2.5 Flash Preview
        GeminiConfig::create([
            'name' => 'gemini-2.5-flash-preview',
            'display_name' => 'Gemini 2.5 Flash Preview',
            'endpoint_url' => env('GEMINI_API_ENDPOINT_2_5_FLASH', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent'),
            'description' => 'Gemini 2.5 Flash preview with advanced spatial understanding',
            'is_active' => true,
            'is_default' => false,
            'temperature' => 0.1,
            'max_tokens' => 4096,
            'max_output_tokens' => 8192,
            'priority' => 90,
            'supports_vision' => true,
            'supports_video' => true,
            'supports_audio' => false,
            'safety_settings' => [
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
            ],
            'generation_config' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 8192,
                'topP' => 0.95,
                'topK' => 64,
            ]
        ]);

        // Gemini 2.5 Pro Preview
        GeminiConfig::create([
            'name' => 'gemini-2.5-pro-preview-03-25',
            'display_name' => 'Gemini 2.5 Pro Preview',
            'endpoint_url' => env('GEMINI_API_ENDPOINT_PRO', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-preview-03-25:generateContent'),
            'description' => 'Gemini 2.5 Pro preview model with cutting-edge capabilities',
            'is_active' => true,
            'is_default' => false,
            'temperature' => 0.05,
            'max_tokens' => 8192,
            'max_output_tokens' => 16384,
            'priority' => 80,
            'supports_vision' => true,
            'supports_video' => true,
            'supports_audio' => true,
            'safety_settings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_LOW_AND_ABOVE'
                ]
            ],
            'generation_config' => [
                'temperature' => 0.05,
                'maxOutputTokens' => 16384,
                'topP' => 0.9,
                'topK' => 40,
            ]
        ]);

        // Gemini 1.5 Flash (Fallback)
        GeminiConfig::create([
            'name' => 'gemini-1.5-flash',
            'display_name' => 'Gemini 1.5 Flash',
            'endpoint_url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
            'description' => 'Gemini 1.5 Flash as fallback option',
            'is_active' => false,
            'is_default' => false,
            'temperature' => 0.1,
            'max_tokens' => 4096,
            'max_output_tokens' => 8192,
            'priority' => 50,
            'supports_vision' => true,
            'supports_video' => false,
            'supports_audio' => false,
            'safety_settings' => [
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
            ],
            'generation_config' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 8192,
                'topP' => 0.95,
                'topK' => 64,
            ]
        ]);

        $this->command->info('Gemini configurations seeded successfully!');
        $this->command->info('Active configurations:');
        
        $activeConfigs = GeminiConfig::active()->orderBy('priority', 'desc')->get();
        foreach ($activeConfigs as $config) {
            $this->command->info("- {$config->display_name} ({$config->name}) - Priority: {$config->priority}");
        }
    }
}
