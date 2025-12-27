<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Storage;

trait StorePDF
{
    public function PDFFileStore($pdf, $pdfFileId)
    {

        $content = $pdf->download()->getOriginalContent();
        $parentDirectory = 'public/invoices/' . $pdfFileId . '/';
        $files = Storage::allFiles($parentDirectory);
        $pdfFileName = $pdfFileId . "_" . (count($files) + 1) . '.pdf';
        Storage::put($parentDirectory . $pdfFileName, $content);

    }
}