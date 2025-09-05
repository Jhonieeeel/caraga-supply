<?php


namespace App\Services\Afms;

class ConvertRisService
{
    public function handle($filepath, $requisition)
    {
        $outputPath = storage_path('app/public/ris');
        $docx = pathinfo($filepath, PATHINFO_FILENAME);
        $pdfFileName = $docx . '.pdf';

        if (!getenv('HOME')) {
            putenv('HOME=' . storage_path());
        }

        $libreOfficePath = '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"';
        $command = "{$libreOfficePath} --headless --convert-to pdf --outdir \"{$outputPath}\" \"{$filepath}\"";

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            logger()->error('LibreOffice PDF conversion failed', [
                'command' => $command,
                'output' => $output,
                'code' => $returnCode,
            ]);
            return false;
        }

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $requisition->pdf = 'ris/' . $pdfFileName;
        $requisition->save();
    }
}
