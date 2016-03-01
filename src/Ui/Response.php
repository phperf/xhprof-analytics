<?php

namespace Phperf\Xhprof\Ui;

use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Content\Error;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Content\Success;
use Yaoi\Io\Content\Text;
use Yaoi\View\Renderer;
use Yaoi\View\Table\HTML;

class Response extends \Yaoi\Io\Response implements Renderer
{
    private $content = '';

    public function error($message)
    {
        $this->addContent(new Error($message));
    }

    public function success($message)
    {
        $this->addContent(new Success($message));
    }

    public function addContent($message)
    {
        $this->content .= $this->renderMessage($message);
    }


    public function renderMessage($message)
    {
        if ($message instanceof SubContent) {
            return '<div class="padded">' . $this->renderMessage($message->content) . '</div>';
        }

        switch (true) {
            case $message instanceof Rows:
                $table = new HTML();
                $table->addClass('table table-striped');
                foreach ($message->getIterator() as $item) {
                    foreach ($item as $key => $value) {
                        $item [$key]= $this->renderMessage($value);
                    }
                    $table->addRow($item);
                }
                return (string)$table;

            case $message instanceof Error:
                return '<div role="alert" class="alert alert-danger"><strong>OOPS!</strong> ' . $message->value . '</div>';

            case $message instanceof Success:
                return '<div role="alert" class="alert alert-success">' . $message->value . '</div>';

            case $message instanceof Anchor:
                return '<a href="' . $message->anchor . '">'
                . $message->value . '</a>';

            case $message instanceof Text:
                return '<div class="text ' . $message->type . '">' . $message->value . '</div>';


            default:
                return '<div>' . $message . '</div>';
        }
    }

    public function isEmpty()
    {
        return empty($this->content);
    }

    public function render()
    {
        echo $this->content;
    }

    public function __toString()
    {
        return $this->content;
    }


}