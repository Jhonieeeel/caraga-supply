<?php


namespace App\Services\Afms;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;


class RequestService {
    public function handle($request) {

        $template = public_path('');

        $sheet = IOFactory::load($template);
        $activeSheet = $sheet->getActiveSheet();



    }
}
