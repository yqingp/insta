<?php
/**
 * Created by PhpStorm.
 * User: kinky
 * Date: 03.05.17
 * Time: 11:39
 */

namespace OxApp\controllers\api;

use Ox\App;
use OxApp\models\TechAccount;

class TechAccountController extends App
{
    /**
     * @return string
     */
    public function get()
    {
        $result = TechAccount::find()->rows;

        return json_encode([
            'data' => $result
        ]);
    }

    /**
     * @return string
     */
    public function post()
    {
        $gmail = TechAccount::data(
            [
                'name' => $this->request->request->get('name'),
                'password' => $this->request->request->get('password'),
                'type' => $this->request->request->get('type')
            ]
        )->add();
        if ($gmail->count === 1) {
            return json_encode([
                'name' => $this->request->request->get('name'),
                'status' => 'success',
                'password' => $this->request->request->get('password')
            ]);
        }

        return json_encode([
            'name' => $this->request->request->get('name'),
            'status' => 'danger',
        ]);
    }

    /**
     * @return string
     */
    public function put()
    {
        $acc = [];
        $acc[] = TechAccount::update([
            'comment' => $this->request->request->get('comment'),
            'count' => $this->request->request->get('count')
        ], [
            'id' => $this->request->request->get('id')
        ]);

        if (count($acc) > 0) {
            $result = ['status' => 200];
        } else {
            $result = ['status' => 500];
        }

        return json_encode($result);
    }
}
