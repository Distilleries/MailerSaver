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

    protected function registerIlluminateMailer()
    {
        $this->app->singleton('mailer', function ($app) {
            $config = $app->make('config')->get('mail');

            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new Mail(
                $app->make(MailModelContract::class), $app['view'], $app['swift.mailer'], $app['events']
            );

            if ($app->bound('queue')) {
                $mailer->setQueue($app['queue']);
            }

            // Next we will set all of the global addresses on this mailer, which allows
            // for easy unification of all "from" addresses as well as easy debugging
            // of sent messages since they get be sent into a single email address.
            foreach (['from', 'reply_to', 'to'] as $type) {
                $this->setGlobalAddress($mailer, $config, $type);
            }

            return $mailer;
        });
    }
}
