<?php
// Helpers pour export
class ExportHelper {
    public static function exportToCsv($data, $filename = 'export.csv') {
        if (empty($data)) {
            return false;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $first = reset($data);
        if (is_array($first)) {
            fputcsv($output, array_keys($first), ';', '"');
            foreach ($data as $row) {
                fputcsv($output, array_values($row), ';', '"');
            }
        }

        fclose($output);
        exit;
    }

    public static function exportToXlsx($data, $filename = 'export.xlsx') {
        // Pour XLSX, utiliser une librairie externe comme PHPExcel ou OpenSpout
        // Pour cette version, utiliser CSV convertible ou JSON
        return self::exportToCsv($data, str_replace('.xlsx', '.csv', $filename));
    }

    public static function generateCsvRow($data) {
        $output = '';
        foreach ($data as $value) {
            $value = str_replace('"', '""', $value);
            $output .= '"' . $value . '",';
        }
        return rtrim($output, ',') . "\n";
    }
}
