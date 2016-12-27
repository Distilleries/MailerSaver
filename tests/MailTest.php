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
    protected $logsPath;

    public function setUp()
    {
        parent::setUp();

        $this->logsPath = base_path('storage/logs') . '/';

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

        $subject = 'subject';
        $bodyMail = 'body content';

        $this->clearLogs();
        Mail::send('mailersaver::default', ['subject' => $subject, 'body_mail' => 'test'], function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(str_contains($this->getLog(), 'foo@example.com'));

        /*
        Mail::send('test', array('data'), function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(true);
        */
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

        $subject = 'subject';
        $bodyMail = 'body content';

        $this->clearLogs();
        Mail::send('mailersaver::default', ['subject' => $subject, 'body_mail' => $bodyMail], function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(str_contains($this->getLog(), 'To: test@test'));

        /*
        Mail::send('test', array('data'), function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Doe')->subject($subject);
        });
        $this->assertTrue(true);
        */
    }

    private function clearLogs()
    {
        foreach (glob($this->logsPath . 'laravel-*') as $path) {
            unlink($path);
        }
    }

    private function getLog()
    {
        foreach (glob($this->logsPath . 'laravel-*') as $path) {
            return file_get_contents($path);
        }

        return '';
    }
}
 