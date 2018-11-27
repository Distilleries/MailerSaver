<?php

require_once __DIR__ . '/../src/models/Email.php';

use App\Email;
use Illuminate\Mail\Markdown;
use Orchestra\Testbench\TestCase;
use Distilleries\MailerSaver\Facades\Mail;
use Wpb\String_Blade_Compiler\ViewServiceProvider;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Distilleries\MailerSaver\MailerSaverServiceProvider;
use Distilleries\MailerSaver\Contracts\MailModelContract;

class MailTest extends TestCase
{
    protected $logsPath;

    protected $emailTo;

    public function setUp()
    {
        parent::setUp();

        $this->logsPath = base_path('storage/logs') . '/';

        $this->emailTo = ['mail' => 'foo@example.com', 'name' => 'John Doe'];

        $this->app->singleton(MailModelContract::class, function ($app) {
            return new Email;
        });

        $this->loadLaravelMigrations(['--database' => 'testing']);

        // call migrations specific to our tests, e.g. to seed the db
        // the path option should be an absolute path.
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../src/database/migrations'),
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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
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

        $this->assertTrue(['mailer', 'swift.mailer', 'swift.transport', Markdown::class,] === $facades);

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

        $this->sendMail();
        $this->assertTrue(str_contains($this->getLog(), 'To: ' . $this->emailTo['name'] . ' <' . $this->emailTo['mail'] . '>'));

        $this->sendErrorMail();
        $this->assertTrue($this->getLog() === '');
    }

    public function testWithOverride()
    {
        $this->app['config']->set('mailersaver', [
            'template' => 'mailersaver::default',
            'override' => [
                'enabled' => true,
                'to' => 'test@test',
                'cc' => [],
                'bcc' => [],
            ],
        ]);

        $this->sendMail();
        $this->assertTrue(str_contains($this->getLog(), 'To: test@test'));

        $this->sendErrorMail();
        $this->assertTrue($this->getLog() === '');
    }

    public function testWithOverrideMultiple()
    {
        $this->app['config']->set('mailersaver', [
            'template' => 'mailersaver::default',
            'override' => [
                'enabled' => true,
                'to' => 'test@test.org,default@mail.org',
                'cc' => [],
                'bcc' => [],
            ],
        ]);

        $this->sendMail();
        $this->assertTrue(str_contains($this->getLog(), 'To: test@test.org, default@mail.org'));
    }

    private function sendMail()
    {
        $subject = 'subject';
        $bodyMail = 'body content';
        $emailTo = $this->emailTo;

        $this->clearLogs();

        Mail::send('mailersaver::default', ['subject' => $subject, 'body_mail' => $bodyMail], function ($message) use ($emailTo, $subject) {
            $message->to($emailTo['mail'], $emailTo['name'])->subject($subject);
        });
    }

    private function sendErrorMail()
    {
        $subject = 'subject';
        $emailTo = $this->emailTo;

        $this->clearLogs();

        Mail::send('test', ['data'], function ($message) use ($emailTo, $subject) {
            $message->to($emailTo['mail'], $emailTo['name'])->subject($subject);
        });
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
 