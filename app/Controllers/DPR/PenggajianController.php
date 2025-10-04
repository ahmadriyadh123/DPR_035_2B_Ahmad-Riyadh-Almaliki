<?php
namespace App\Controllers\DPR;
use App\Controllers\BaseController;

class PenggajianController extends BaseController
{
    public function spaShell()
    {
        $data['title'] = 'Manajemen Penggajian';
        return view('DPR/penggajian/spa_shell', $data);
    }

    public function summary()
    {
        if ($this->request->isAJAX()) {
            $model = new PenggajianModel();
            $data = $model->getSummary();

            return $this->response->setJSON($data);
        }
        // Jika bukan AJAX, tolak akses
        return $this->response->setStatusCode(403, 'Forbidden');
    }
}
