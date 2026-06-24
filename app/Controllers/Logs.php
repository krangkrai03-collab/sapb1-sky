<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;

class Logs extends BaseController
{
    private const PER_PAGE = 20;

    public function index()
    {
        $model = new ActivityLogModel();

        return $this->render('logs/index', [
            'title' => lang('App.logs'),
            'logs'  => $model->orderBy('id', 'DESC')->paginate(self::PER_PAGE),
            'pager' => $model->pager,
            'total' => $model->pager->getTotal(),
        ]);
    }
}
