<?php

namespace Distilleries\MailerSaver\Helpers;

use Swift_Mailer;
use Illuminate\Mail\Mailer;
use Illuminate\Events\Dispatcher;
use Wpb\String_Blade_Compiler\Factory;
use Distilleries\MailerSaver\Contracts\MailModelContract;
use Illuminate\Contracts\Mail\Mailable as MailableContract;

class Mail extends Mailer
{
    /**
     * Mail model instance.
     *
     * @var \Distilleries\MailerSaver\Contracts\MailModelContract
     */
    protected $model;

    /**
     * Configuration override data.
     *
     * @var array
     */
    protected $override;

    /**
     * Mailersaver instance constructor.
     * 
     * @param  \Distilleries\MailerSaver\Contracts\MailModelContract  $model
     * @param  \Wpb\String_Blade_Compiler\Factory  $views
     * @param  \Swift_Mailer  $swift
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function __construct(MailModelContract $model, Factory $views, Swift_Mailer $swift, Dispatcher $events = null)
    {
        $this->model = $model;

        $this->override = config('mailersaver.override');

        parent::__construct($views, $swift, $events);
    }

    /**
     * Render the given mail view.
     *
     * @param  string $view
     * @param  array  $data
     * @return string
     * @throws \Throwable
     */
    protected function renderView($view, $data)
    {
        $body = $this->model->getTemplate($view);
        $body = empty($body) ? $this->views->make($view, $data)->render() : $body;

        $subjectTemplate = $this->model->getSubject();
        if (! empty($subjectTemplate)) {
            $subject = view([
                'template' => $subjectTemplate,
                'cache_key' => uniqid() . rand(),
                'updated_at' => 0,
            ], $data);
            $data['subject'] = $subject->render();
        } else {
            $data['subject'] = $subjectTemplate;
        }

        $data['body_mail'] = view([
            'template' => $body,
            'cache_key' => uniqid(),
            'updated_at' => 0,
        ], $data);

        return $this->views->make(config('mailersaver.template'), $data)->render();
    }

    /**
     * Send contained mail instance.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  mixed  $callback
     * @return void
     */
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        [$view, $plain, $raw] = $this->parseView($view);

        $model = $this->model->initByTemplate($view);
        $template = $model->where('action', $view)->first();
        $plain = ! empty($template) ? $template->getPlain() : $plain;

        if (! empty($template)) {
            $this->model = $template;
        }

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        call_user_func($callback, $message);

        $this->addContent($message, $view, $plain, $raw, $data);
        
        $this->addSubject($message);
        $this->addCc($message);
        $this->addBcc($message);
        $this->overrideTo($message);

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
        $swiftMessage = $message->getSwiftMessage();

        if ($this->shouldSendMessage($swiftMessage, $data)) {
            $this->sendSwiftMessage($swiftMessage);

            $this->dispatchSentEvent($message, $data);
        }
    }

    /**
     * Set subject to message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    public function addSubject($message)
    {
        $subject = $this->model->getSubject();
        if (! empty($subject)) {
            $message->setSubject($subject);
        }
    }

    /**
     * Set CC addresses to message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    public function addCc($message)
    {
        $cc = $this->isOverride() ? $this->override['cc'] : (! empty($this->model) ? $this->model->getCc() : '');
        if (! empty($cc)) {
            $message->setCc($cc);
        }
    }

    /**
     * Set BCC addresses to message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    public function addBcc($message)
    {
        $bcc = $this->isOverride() ? $this->override['bcc'] : $this->model->getBcc();
        if (! empty($bcc)) {
            $message->setBcc($bcc);
        }
    }

    /**
     * Set TO addresses to message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    public function overrideTo($message)
    {
        $to = $this->isOverride() ? explode(',', $this->override['to']) : [];
        if (! empty($to)) {
            $message->setTo($to);
        }
    }

    /**
     * Return if mail configuration is supercharged.
     *
     * @return bool
     */
    public function isOverride()
    {
        return (bool) $this->override['enabled'];
    }
} 