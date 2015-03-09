<?php

namespace Fbn\Silex;

use Symfony\Component\Console\Helper\Helper;
use Silex\Application;

class ApplicationConsoleHelper extends Helper {
    
    protected $app;

	/**
	 * 
	 * @param Application $app
	 */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
