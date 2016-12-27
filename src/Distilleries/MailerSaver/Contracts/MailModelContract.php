<?php

namespace Distilleries\MailerSaver\Contracts;

interface MailModelContract
{
	/**
     * Scope a new query with given action.
     *
     * @param  string  $view
     * @return \Illuminate\Database\Query\Builder
     */
    public function initByTemplate($view);

    /**
     * Return instance content if exists.
     *
     * @param  string  $view
     * @return string
     */
    public function getTemplate($view);

    /**
     * Return plain text version of mail body.
     *
     * @return string
     */
    public function getPlain();

    /**
     * Get current instance subject.
     *
     * @return string
     */
    public function getSubject();

    /**
     * Get configured CC emails of current instance.
     *
     * @return array
     */
    public function getCc();
    
    /**
     * Get configured CC emails of current instance.
     *
     * @return array
     */
    public function getBcc();
}
