<?php

namespace CanadasMotorcycle;

/**
 * Class App.
 *
 * To save from over-engineering this basic PHP test, this will more or less
 * act as the controller, but it's not a controller. This is to make the setup
 * easier, and avoid wasting time reinventing MVC.
 *
 * @package CanadasMotorcycle
 */
class App
{
    /**
     * @var Model $model The Model object.
     */
    private $model;

    /**
     * @var View $view The View object.
     */
    private $view;

    /**
     * Get things rolling.
     */
    public function __construct() {
        $this->model = new Model();
        $this->view = new View();
    }


    public function start() {

        //echo $this->view->render();

        echo $this->view->fetchView('cart');

    }
}
