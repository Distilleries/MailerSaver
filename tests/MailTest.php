<?php

require_once(__DIR__.'/../src/models/Email.php');


/**
 * Created by PhpStorm.
 * User: cross
 * Date: 2/25/2015
 * Time: 12:51 PM
 */

use \Mockery as m;

class MailTest extends \Orchestra\Testbench\TestCase {


    public function setUp(){

        parent::setUp();

        $this->app->singleton('Distilleries\MailerSaver\Contracts\MailModelContract', function ($app)
        {
            return new \App\Email;
        });

        $this->app['Illuminate\Contracts\Console\Kernel']->call('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../src/database/migrations'),
        ]);

        \App\Email::create(['action' => 'test', 'libelle' => 'test', 'body_type' => 'html', 'cc' => 'testcc@test', 'bcc' => 'testbcc@test', 'content' => 'test', 'status' => 0]);

    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ));
    }

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

    public function testWithoutOverride()
    {
        $this->app['config']->set('mailersaver.mail', [
            'template' => 'mailersaver::admin.templates.mails.default',
            'override' => [
                'enabled' => false,
                'to' => [],
                'cc' => [],
                'bcc' => []
            ]
        ]);

        $logger = m::mock('Psr\Log\LoggerInterface');
        $logger->shouldReceive('info')->twice()->with('Pretending to mail message to: foo@example.com');
        
        \Mail::setLogger($logger);
        \Mail::pretend();

        \Mail::send('mailersaver::admin.templates.mails.default', ['subject' => 'test', 'body_mail' => 'test' ], function ($m) {
            $m->to('foo@example.com', 'John Smith')->subject('Welcome!');
        });

        \Mail::send('test', array('data'), function ($m) {
            $m->to('foo@example.com', 'John Smith')->subject('Welcome!');
        });
    }
    public function testWithOverride()
    {
        $this->app['config']->set('mailersaver.mail', [
            'template' => 'mailersaver::admin.templates.mails.default',
            'override' => [
                'enabled' => true,
                'to' => ['test@test'],
                'cc' => [],
                'bcc' => []
            ]
        ]);

        $logger = m::mock('Psr\Log\LoggerInterface');
        $logger->shouldReceive('info')->twice()->with('Pretending to mail message to: test@test');

        \Mail::setLogger($logger);
        \Mail::pretend();

        \Mail::send('mailersaver::admin.templates.mails.default', ['subject' => 'test', 'body_mail' => 'test' ], function ($m) {
            $m->to('foo@example.com', 'John Smith')->subject('Welcome!');
        });

        \Mail::send('test', array('data'), function ($m) {
            $m->to('foo@example.com', 'John Smith')->subject('Welcome!');
        });
    }
}
 