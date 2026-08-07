<?php

// app/Support/CsvExport.php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExport
{
    /**
     * Stream a CSV download — Excel opens this natively as a normal spreadsheet. $rows is an
     * array of arrays; scalar values are written as-is (numbers stay unquoted/sortable).
     */
    public static function download(string $filename, array $headers, array $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel renders non-ASCII (e.g. Arabic names) correctly
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
