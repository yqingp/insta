<?php
/**
 * Created by PhpStorm.
 * User: kinky
 * Date: 18.04.17
 * Time: 10:21
 */

namespace OxApp\controllers;

use Ox\App;
use Ox\View;
use OxApp\models\TaskType;

/**
 * Class TaskType
 * @package OxApp\controllers
 */
class TaskTypeController extends App
{
    /**
     * GET method
     */
    public function get()
    {
        $taskTypes = TaskType::find()->rows;
        return View::build("taskType", ['taskTypes' => $taskTypes]);

    }

    /**
     * POST method
     */
    public function post()
    {
        //
    }
}
