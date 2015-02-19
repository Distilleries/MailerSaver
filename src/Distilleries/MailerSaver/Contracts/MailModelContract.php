<?php namespace Distilleries\MailerSaver\Contracts;

interface MailModelContract {

    public function initByTemplate($view);

    public function getTemplate($view);

    public function getBcc();

    public function getSubject();

    public function getCc();

    public function getPlain();
}