<?php

echo '1';

register_shutdown_function(function(){
    echo '2';
    register_shutdown_function(function(){
        echo 'the endest', PHP_EOL;
    });
});

register_shutdown_function(function(){
    echo 'intermediate', PHP_EOL;
});

