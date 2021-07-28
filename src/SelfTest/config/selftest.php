<?php

return [
    'app' => [
        'title' => 'Self Test Page',
    ],
    'plugins' => [
        
        //List the test classes you want to run. Add more of your own as well
        \Jsantoso\LaravelServices\SelfTest\Plugins\DatabaseTest::class,
        \Jsantoso\LaravelServices\SelfTest\Plugins\RedisTest::class,
        \Jsantoso\LaravelServices\SelfTest\Plugins\AWSSQSTest::class,
        \Jsantoso\LaravelServices\SelfTest\Plugins\TempFileTest::class,
        \Jsantoso\LaravelServices\SelfTest\Plugins\LaravelQueueTest::class,
        \Jsantoso\LaravelServices\SelfTest\Plugins\EmailTest::class,

        //Add any custom plugins below 
    ]
];
