<?php

class Home {

    function index() {
        $f3 = Base::instance();
        $f3->set('title', 'SwitchedHit - Home');
        echo Template::instance()->render('index.html');
    }

}

?>