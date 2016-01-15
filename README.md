[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Distilleries/MailerSaver/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/MailerSaver/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Distilleries/MailerSaver/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/MailerSaver/?branch=master)
[![Build Status](https://travis-ci.org/Distilleries/MailerSaver.svg?branch=master)](https://travis-ci.org/Distilleries/MailerSaver)
[![Total Downloads](https://poser.pugx.org/distilleries/mailersaver/downloads)](https://packagist.org/packages/distilleries/mailersaver)
[![Latest Stable Version](https://poser.pugx.org/distilleries/mailersaver/version)](https://packagist.org/packages/distilleries/mailersaver)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)



#Laravel 5 Mailer saver
Mailer saver extend the laravel 5 mailer.

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
        "distilleries/mailersaver": "2.*",
    }
```

run `composer update`.

Add Service provider to `config/app.php`:

``` php
    'providers' => [
        // ...
       Distilleries\MailerSaver\MailerSaverServiceProvider::class,
       Wpb\String_Blade_Compiler\ViewServiceProvider::class
    ]
```

And Facade (also in `config/app.php`) replace the laravel facade `Mail`
   

``` php
    'aliases' => [
        // ...
       'Mail' => 'Distilleries\MailerSaver\Facades\Mail',
    ]
```

You need to provide a model of data, simply add on your register method a new instance of your model in your `app/Providers/AppServiceProvider.php`:

``` php
    public function register()
	{

		$this->app->singleton('Distilleries\MailerSaver\Contracts\MailModelContract', function ($app)
        {
            return new \App\Email;
        });

	}
```

In this case I return a Email model instance.
This model just implement the contract `\Distilleries\MailerSaver\Contracts\MailModelContract`.

To Publish the model:

```ssh
php artisan vendor:publish --provider="Distilleries\MailerSaver\MailerSaverServiceProvider" --tag="models"
```

To Publish the migration:

```ssh
php artisan vendor:publish --provider="Distilleries\MailerSaver\MailerSaverServiceProvider" --tag="migrations"
```


##Config file

You can publish the config file with the command line:

```ssh
php artisan vendor:publish --provider="Distilleries\MailerSaver\MailerSaverServiceProvider"
```


```php
    return [
        'mail'                => [
            'template' => 'mailersaver::admin.templates.mails.default',
            'override' => [
                'enabled' => false,
                'to'      => [],
                'cc'      => [],
                'bcc'     => []
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
Before modify it you have to publish it:

```ssh
php artisan vendor:publish --provider="Distilleries\MailerSaver\MailerSaverServiceProvider" --tag="views"
```


##Send an email
It's exactly the same than the laravel mailer.

Example:

```php
Mail::send('emails.welcome', array('key' => 'value'), function($message)
{
    $message->to('foo@example.com', 'John Smith')->subject('Welcome!');
});
```

If the override is set to true email is send to another `to` email address.

##Troubleshooting

If composer update --require-dev refuse to install, remove illuminate/* from vendor before the install or just remove vendor and start fresh.
