<?php

namespace Distilleries\MailerSaver;

use Illuminate\Mail\MailServiceProvider;
use Distilleries\MailerSaver\Helpers\Mail;
use Distilleries\MailerSaver\Contracts\MailModelContract;

class MailerSaverServiceProvider extends MailServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../../views', 'mailersaver');

        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('mailersaver.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../../views' => base_path('resources/views/vendor/mailersaver'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../../models/Email.php' => base_path('app/Email.php'),
        ], 'models');

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php', 'mailersaver'
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mailer', function ($app) {
            $this->registerSwiftMailer();

            $model = $app->make(MailModelContract::class);
            $mailer = new Mail($model, $app['view'], $app['swift.mailer'], $app['events']);

            $this->setMailerDependencies($mailer, $app);

            $from = $app['config']['mail.from'];
            if (is_array($from) and isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            $to = $app['config']['mail.to'];
            if (is_array($to) and isset($to['address'])) {
                $mailer->alwaysTo($to['address'], $to['name']);
            }

            return $mailer;
        });
    }
}
