<?php

namespace Phperf\Xhprof\Html\View;


use Yaoi\View\Hardcoded;
use Yaoi\View\Renderer;
use Yaoi\View\Stack;

class Layout extends Hardcoded
{
    public function isEmpty()
    {
        return false;
    }

    public $title;

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function __construct()
    {
        $this->main = new Stack();
    }

    /** @var Stack  */
    private $main;

    public function pushMain(Renderer $block) {
        $this->main->push($block);
        return $this;
    }

    public function render()
    {
?>
<!doctype html>
<html>
<head>
    <title><?=$this->title?></title>
</head>

<body>
<?php echo $this->main ?>
</body>
</html>


<?php
    }

}