<?php

namespace Phperf\Xhprof\Ui;

use Yaoi\Io\Content\Error;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\SubContent;
use Yaoi\Io\Content\Success;
use Yaoi\Io\Content\Text;
use Yaoi\View\Table\HTML;

class Response extends \Yaoi\Io\Response
{
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
        if ($message instanceof SubContent) {
            echo '<div class="padded">';
            $this->addContent($message->content);
            echo '</div>';
            return $this;
        }

        if ($message instanceof Rows) {
            $table = new HTML();
            foreach ($message->getIterator() as $item) {
                $table->push($item);
            }
            $message = (string)$table;
        } elseif ($message instanceof Text) {
            $message = '<div class="text '.$message->type.'">' . $message->value . '</div>';
        }

        echo '<div>' . $message . '</div>';
        return $this;
    }


}