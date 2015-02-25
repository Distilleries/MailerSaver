<?php

require_once('models/Email.php');


/**
 * Created by PhpStorm.
 * User: cross
 * Date: 2/25/2015
 * Time: 12:51 PM
 */

use \Mockery as m;

class MailTest extends \Orchestra\Testbench\TestCase {


    protected function getPackageProviders()
    {
        return array('Distilleries\MailerSaver\MailerSaverServiceProvider');
    }

    protected function getPackageAliases()
    {
        return [
            'Mail' => 'Distilleries\MailerSaver\Facades\Mail'
        ];
    }
    public function testServiceProvider()
    {

        $service = $this->app->getProvider('Distilleries\MailerSaver\MailerSaverServiceProvider');

        $facades = $service->provides();
        $this->assertTrue(['mailer', 'swift.mailer', 'swift.transport'] == $facades);

        $service->boot();
        $service->register();

    }

    public function testFacade()
    {
        $this->app->singleton('Distilleries\MailerSaver\Contracts\MailModelContract', function ($app)
        {
            return new ;
        });

        $service = $this->app->getProvider('Distilleries\MailerSaver\MailerSaverServiceProvider');



        $this->app['mailer'];
        $facades = $service->provides();
        $this->assertTrue(['mailer', 'swift.mailer', 'swift.transport'] == $facades);

        $service->boot();
        $service->register();

    }

}
 