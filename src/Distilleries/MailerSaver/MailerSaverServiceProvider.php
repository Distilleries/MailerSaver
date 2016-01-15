<?php namespace Distilleries\MailerSaver;

use Distilleries\MailerSaver\Helpers\Mail;

class MailerSaverServiceProvider extends \Illuminate\Mail\MailServiceProvider {


    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
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

    public function register()
    {

        $this->app->singleton('mailer', function ($app) {
            $this->registerSwiftMailer();

            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.

            $model  = $app->make('Distilleries\MailerSaver\Contracts\MailModelContract');
            $mailer = new Mail(
                $model, $app['config'], $app['view'], $app['swift.mailer'], $app['events']
            );

            $this->setMailerDependencies($mailer, $app);

            // If a "from" address is set, we will set it on the mailer so that all mail
            // messages sent by the applications will utilize the same "from" address
            // on each one, which makes the developer's life a lot more convenient.
            $from = $app['config']['mail.from'];

            if (is_array($from) && isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            $to = $app['config']['mail.to'];

            if (is_array($to) && isset($to['address'])) {
                $mailer->alwaysTo($to['address'], $to['name']);
            }

            return $mailer;
        });


    }
}