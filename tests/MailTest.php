<?php

require_once __DIR__ . '/../src/models/Email.php';

use Mockery;
use App\Email;
use Orchestra\Testbench\TestCase;
use Distilleries\MailerSaver\Facades\Mail;
use Wpb\String_Blade_Compiler\ViewServiceProvider;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Distilleries\MailerSaver\MailerSaverServiceProvider;
use Distilleries\MailerSaver\Contracts\MailModelContract;

class MailTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->singleton(MailModelContract::class, function ($app) {
            return new Email;
        });

        $this->app[Artisan::class]->call('migrate', [
            '--realpath' => realpath(__DIR__.'/../src/database/migrations'),
        ]);

        Email::create([
            'action' => 'test',
            'libelle' => 'test',
            'body_type' => 'html',
            'cc' => 'testcc@test',
            'bcc' => 'testbcc@test',
            'content' => 'test',
            'status' => 0,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ViewServiceProvider::class,
            MailerSaverServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Mail' => Mail::class,
        ];
    }
    public function testServiceProvider()
    {
        $service = $this->app->getProvider(MailerSaverServiceProvider::class);
        $facades = $service->provides();

        $this->assertTrue(['mailer', 'swift.mailer', 'swift.transport'] === $facades);

        $service->boot();
        $service->register();
    }

    public function testWithoutOverride()
    {
        $this->app['config']->set('mailersaver', [
            'template' => 'mailersaver::default',
            'override' => [
                'enabled' => false,
            ],
        ]);

        $subject = 'test';
        $bodyMail = 'test';

        Mail::send('mailersaver::default', ['subject' => $subject, 'body_mail' => 'test'], function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(true);

        Mail::send('test', array('data'), function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(true);
    }

    public function testWithOverride()
    {
        $this->app['config']->set('mailersaver', [
            'template' => 'mailersaver::default',
            'override' => [
                'enabled' => true,
                'to' => ['test@test'],
                'cc' => [],
                'bcc' => [],
            ],
        ]);

        $subject = 'test';
        $bodyMail = 'test';

        Mail::send('mailersaver::default', ['subject' => $subject, 'body_mail' => $bodyMail], function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(true);

        Mail::send('test', array('data'), function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(true);
    }
}
 