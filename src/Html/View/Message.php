<?php

namespace Phperf\Xhprof\Html\View;


use Yaoi\View\Hardcoded;

class Message extends Hardcoded
{
    private $message;
    const TYPE_CLASS = 'message';

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function render()
    {
        ?><p class="<?=self::TYPE_CLASS?>"><?=$this->message?></p><?php
    }

}