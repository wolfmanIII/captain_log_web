<?php

namespace App\Service;

use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class DocumentTextExtractor
{
    public function __construct(
        private PdfParser $pdfParser,
    ) {}

    public function extract(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf'  => $this->extractFromPdf($path),
            'md'   => $this->extractFromMarkdown($path),
            'odt'  => $this->extractFromOdt($path),
            'docx' => $this->extractFromDocx($path),
            default => null,
        };
    }

    private function extractFromPdf(string $path): ?string
    {
        try {
            $pdf  = $this->pdfParser->parseFile($path);
            $text = $pdf->getText();
            $text = $this->normalizeWhitespace($text);

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function extractFromMarkdown(string $path): ?string
    {
        $text = @file_get_contents($path);
        if ($text === false) {
            return null;
        }

        $text = preg_replace('/!\[[^\]]*]\([^)]*\)/', '', $text);
        $text = preg_replace('/<img[^>]*>/i', '', $text);

        $text = $this->normalizeWhitespace($text);

        return $text !== '' ? $text : null;
    }

    private function extractFromOdt(string $path): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $contentXml = $zip->getFromName('content.xml');
        $zip->close();

        if ($contentXml === false) {
            return null;
        }

        $dom = new \DOMDocument();
        $dom->loadXML($contentXml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');

        $nodes = $xpath->query('//text:p | //text:h');
        $lines = [];

        foreach ($nodes as $node) {
            $lines[] = $node->textContent;
        }

        $text = implode("\n", $lines);
        $text = $this->normalizeWhitespace($text);

        return $text !== '' ? $text : null;
    }

    private function extractFromDocx(string $path): ?string
    {
        try {
            $phpWord = PhpWordIOFactory::load($path);
        } catch (\Throwable $e) {
            return null;
        }

        $textParts = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
                    continue;
                }

                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    $runText = [];
                    foreach ($element->getElements() as $child) {
                        if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                            $runText[] = $child->getText();
                        }
                    }
                    if ($runText) {
                        $textParts[] = implode(' ', $runText);
                    }
                    continue;
                }
                
                /** @var SomeClassWithGetText|mixed $element */
                if (method_exists($element, 'getText')) {
                    $textParts[] = $element->getText();
                }
            }
        }

        $text = implode("\n", $textParts);
        $text = $this->normalizeWhitespace($text);

        return $text !== '' ? $text : null;
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = str_replace("\r", "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{2,}/', "\n\n", $text);

        return trim($text);
    }
}
