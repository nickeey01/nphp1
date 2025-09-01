<?php
    require 'ClassAutoLoad.php';

    // Using the class methods
    print $layout->header($conf);
    print $hello->today();
    $form->signup();
    print $layout->footer($conf);
