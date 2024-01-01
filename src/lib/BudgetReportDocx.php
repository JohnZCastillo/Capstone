<?php

namespace App\lib;

use PhpOffice\PhpWord\TemplateProcessor;

class BudgetReportDocx
{

    public static function generate(array $data, array $summary)
    {

        $dir = __DIR__ . '/../../template/';

        $templateFile = $dir . 'budget_template.docx';

        $templateProcessor = new TemplateProcessor($templateFile);

        $values = [];

        foreach ($data as  $value) {
            $values [] = $value;
        }

        $templateProcessor->cloneRowAndSetValues('TITLE', $values);

        $templateProcessor->setValue('TOTAL', $summary['TOTAL']);
        $templateProcessor->setValue('INCOME', $summary['INCOME']);
        $templateProcessor->setValue('EXPENSE', $summary['EXPENSE']);

        $outputFile = $dir . './test.docx';

        $templateProcessor->saveAs($outputFile);

        return $outputFile;
    }

}