<?php
/**
 * Created by PhpStorm.
 * User: kinkytail
 * Date: 06.04.17
 * Time: 11:12
 */

namespace OxApp\controllers;

use Ox\App;
use Ox\View;
use OxApp\models\Proxy;

class ProxyController extends App
{
    protected $alerts = [];
    /**
     * Get method
     */
    public function get()
    {
        $proxy = Proxy::orderBy(["id" => "desc"])->find();
        return View::build('proxy', ['proxyes' => $proxy->rows, 'alerts' => $this->alerts]);
    }

    /**
     * POST method
     */
    public function post()
    {
        for ($i = $this->request->request->get('portIn'); $i < $this->request->request->get('portOut'); $i++) {
            Proxy::add([
                'proxy' => $this->request->request->get('ip') . ":" . $i . ";" . $this->request->request->get('authData'),
            ]);
        }
        $this->alerts = ['success' => 'proxy Add'];

        return $this->get();
    }
}
