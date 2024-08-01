<?php
// src/Service/CsvExporter.php
namespace App\Service;

class CsvExporter
{
    public function export(array $data, string $filename): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $output = fopen('php://output', 'wb');

        if (!empty($data)) {
// Add header row
            fputcsv($output, array_keys($data[0]));

// Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}

