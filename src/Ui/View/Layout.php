<?php

namespace Phperf\Xhprof\Ui\View;


use Yaoi\View\Hardcoded;
use Yaoi\View\Renderer;

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

    /** @var  Renderer  */
    private $main;

    public function setMain(Renderer $main) {
        $this->main = $main;
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