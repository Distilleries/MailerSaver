<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Distilleries\MailerSaver\Contracts\MailModelContract;

/**
 * @property integer $id
 * @property string $libelle
 * @property string $body_type
 * @property string $action
 * @property string $cc
 * @property string $bcc
 * @property string $content
 * @property boolean $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class Email extends Model implements MailModelContract
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'libelle',
        'body_type',
        'action',
        'cc',
        'bcc',
        'content',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'status' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * {@inheritdoc}
     */
    public function initByTemplate($view)
    {
        return $this->where('action', '=', $view);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($view)
    {
        if (! empty($this->action)){
            return $this->content;
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPlain()
    {
        return mb_strtolower($this->body_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->libelle;
    }

    /**
     * {@inheritdoc}
     */
    public function getCc()
    {
        return ! empty($this->cc) ? explode(',', $this->cc) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getBcc()
    {
        return ! empty($this->bcc) ? explode(',', $this->bcc) : [];
    }
}
