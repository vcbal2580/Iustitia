<?php
try{
    $loader = new Phalcon\loader();
    $loader->registerDirs(array(
        '../app/controllers',
            '../app/models',
            '../api/controllers',
            '../api/models',
            '../cli/controllers',
            '../cli/models',
        )
    )->register();

    $di = new Phalcon\Di\FactoryDefault();
    $di->set('view',function ()
    {
        $view = new Phalcon\Mvc\View();
        $view->setViewsDir('../app/views/');
        return $view;
    });

    $di->set('url',function ()
    {
        $url = new Phalcon\Mvc\Url();
        $url->setBaseUri('/Iustitia/');
        return $url;
    });

    $application = new Phalcon\Mvc\Application($di);
    echo $application->handle()->getContent();
}catch (\Exception $e){
    echo 'Exception : '.$e->getMessage();
}