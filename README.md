#Laravel 4 Mailer saver
Mailer saver extend the laravel 4 mailer.

* Add the possibility to override the to,cc,bcc of your mail without modify your implementation.
* Add the possibility to get your template mail, subject, cc, bcc and type from a model.



## Table of contents
1. [Installation](#installation)
2. [Config file](#config-file)
3. [View](#view)
4. [Send an email](#send-an-email)


##Installation

Add on your composer.json

``` json
    "require": {
        "distilleries/mailersaver": "1.*",
    }
```

run `composer update`.

Add Service provider to `config/app.php`:

``` php
    'providers' => [
        // ...
       'Distilleries\MailerSaver\MailerSaverServiceProvider',
    ]
```

And Facade (also in `config/app.php`) replace the laravel facade `Mail`
   

``` php
    'aliases' => [
        // ...
       'Mail' => 'Distilleries\MailerSaver\Facades\Mail',
    ]
```

You need to provide a model of data, simply add on your register method a new instance of your model:

``` php
    public function register()
	{

		$this->app->bindShared('Distilleries\MailerSaver\Contracts\MailModelContract', function ($app)
		{
			return new \Email;
		});

	}
```

In this case I return a Email model instance.
This model just implement the contract.


``` php
    class Email extends Eloquent implements \Distilleries\MailerSaver\Contracts\MailModelContract {
    
        use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    
        protected $fillable = [
            'libelle',
            'body_type',
            'action',
            'cc',
            'bcc',
            'content'
        ];
    
        public function initByTemplate($view)
        {
            return $this->where('action', '=', $view)->get()->last();
        }
    
        public function getTemplate($view)
        {
            if (!empty($this->action))
            {
                return $this->content;
            }
    
            return '';
        }
    
        public function getBcc()
        {
            return !empty($this->bcc) ? explode(',', $this->bcc) : [];
        }
    
        public function getSubject()
        {
            return $this->libelle;
        }
    
        public function getCc()
        {
            return !empty($this->cc) ? explode(',', $this->cc) : [];
        }
    
        public function getPlain()
        {
            return strtolower($this->body_type);
        }
    
    
    }
```

To work with this model I created a migration:

``` php
    <?php
    
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    
    class CreateEmailsTable extends Migration {
    
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('emails', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('libelle');
                $table->string('body_type');
                $table->string('action');
                $table->text('cc');
                $table->text('bcc');
                $table->text('content');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::drop('emails');
        }
    
    }
```

##Config file

You can publish the config file with the command line:

```ssh
php artisan config:publish distilleries/mailersaver
```


```php
    return [
        'mail'                => [
            'template' => 'mailersaver::admin.templates.mails.default',
            'override' => [
                'enabled' => false,
                'to'      => [''],
                'cc'      => [''],
                'bcc'     => ['']
            ]
        ],
    ];
```


Field | Description
----- | -----------
template | Global template when you put the content of your mail.
override | An array with all the config to hoock the mail send.
enabled | Enable the override of the mail. If in true that send the email with the to, cc, bcc
to | Use to send an email whn the override set to true
cc | Use to send an email whn the override set to true
bcc | Use to send an email whn the override set to true



##View
To override the view you can give a new template on the configuration or modify the current one.
Before modify it you have to publish it

```ssh
php artisan view:publish distilleries/mailersaver
```


##Send an email
It's exactly the same of laravel mailer.

Example:

```php
Mail::send('emails.welcome', array('key' => 'value'), function($message)
{
    $message->to('foo@example.com', 'John Smith')->subject('Welcome!');
});
```

If the override is set to true email is send to another `to` email address.