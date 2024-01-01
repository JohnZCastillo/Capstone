<?php

namespace App\lib;

use PhpOffice\PhpWord\TemplateProcessor;

class DocxMaker
{
    private const DIR = __DIR__ . '/../../template/';
    protected string $fileName;
    protected TemplateProcessor $templateProcessor;
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->templateProcessor = new TemplateProcessor(self::DIR . $fileName);
    }
    public function addBody(array $data, string $target): void
    {
        $this->templateProcessor->cloneRowAndSetValues($target, $data);
    }

    public function addHeader(array $headers): void
    {
        foreach ($headers as $header => $value){
            $this->templateProcessor->setValue($header, $value);
        }
    }
    public function output(): string
    {
        $outputFile = self::DIR . 'output_' . $this->fileName;

        $this->templateProcessor->saveAs($outputFile);

        return $outputFile;
    }

}