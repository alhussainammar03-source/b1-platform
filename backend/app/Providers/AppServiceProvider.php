<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Writing\HandwritingExtractionService;
use App\Services\Writing\OpenAIHandwritingExtractionService;
use App\Services\Writing\WritingEvaluationService;
use App\Services\Writing\OpenAIWritingEvaluationService;
use App\Services\Speaking\Contracts\SpeakingAiProvider;
use App\Services\Speaking\Providers\FakeSpeakingAiProvider;
use App\Services\Speaking\Providers\OpenAiSpeakingProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            HandwritingExtractionService::class,
            OpenAIHandwritingExtractionService::class
        );

        $this->app->bind(
            WritingEvaluationService::class,
            OpenAIWritingEvaluationService::class
        );



        $this->app->singleton(
            SpeakingAiProvider::class,
            function ($app) {
                return match (config('services.speaking.ai_provider')) {
                    'openai' => $app->make(OpenAiSpeakingProvider::class),
                    'fake' => $app->make(FakeSpeakingAiProvider::class),

                    default => throw new \RuntimeException(
                        'Unsupported speaking AI provider: '
                            . config('services.speaking.ai_provider')
                    ),
                };
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
