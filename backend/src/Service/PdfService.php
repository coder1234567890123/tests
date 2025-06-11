<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Company;
use App\Entity\Subject;
use Knp\Snappy\Pdf;
use Knp\Snappy\AbstractGenerator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use mikehaertl\pdftk\Pdf as PDFTK;

/**
 * Class PdfService
 *
 * @package App\Service
 */
class PdfService extends Pdf
{
    /**
     * @var Pdf
     */
    private $snappy;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PdfService constructor.
     *
     * @param Pdf $snappy
     */
    public function __construct(Pdf $snappy, LoggerInterface $logger)
    {
        $this->snappy = $snappy;
        $this->logger = $logger;
    }

    /**
     * @param string  $cover
     * @param string  $html
     * @param Company $company
     * @param string  $header
     * @param string  $footer
     * @param string  $ownerPassword
     * @param string  $userPassword
     *
     * @return mixed
     */
    public function generatePdf(string $cover = null, string $html, Company $company = null, string $header = null,
                                string $footer = null, string $userPassword = null)
    {
        $this->setDefaultOptions();
        $pdfFileName = $this->snappy->createTemporaryFile('temp'); // set unique name for file
        $coverPdf = $cover ? $this->createCover($cover) : null;
        $mainDoc = $this->createMainDoc($html, $header, $footer);

        $tmp = new PDFTK(null, ['useExec' => true]); // join cover page and main doc (they have different margins)
        if ($coverPdf) {
            $tmp->addFile($coverPdf, 'A')
                ->addFile($mainDoc, 'B')
                ->cat(null, null, 'A')
                ->cat(null, null, 'B');
        } else {
            $tmp->addFile($mainDoc, 'B');
        }
        $tmp->saveAs($pdfFileName);

        $this->logger->debug(sprintf('Combine cover page and main page at "%s"', $pdfFileName));

        $content = $tmp->toString();
        $this->snappy->removeTemporaryFiles();
        return $content;
        //if the Client wants passwords added to the pdf's again
//        if ($company->isPasswordSet()) {
//            return $this->setPassword($company->getPdfPassword(), $pdfFileName);
//        } else {
//            $content = $tmp->toString();
//            $this->snappy->removeTemporaryFiles();
//            return $content;
//        }
    }

    /**
     * @param string $cover
     *
     * @return string
     */
    private function createMainDoc($html, $header, $footer)
    {
        $htmlFilename = '';
        if ($html) {
            $options = [ // main pdf margins
                'margin-bottom' => '30mm',
                'margin-top' => '10mm',
                'margin-left' => '0mm',
                'margin-right' => '0mm'
            ];
            $this->includeHeader($header);
            $this->includeFooter($footer);

            $htmlPdf = $this->snappy->getOutputFromHtml($html, $options);

            $htmlFilename = $this->snappy->createTemporaryFile($htmlPdf); // create main pdf
            $this->logger->debug(sprintf('Create pdf main pages at "%s"', $htmlFilename), ['options' => $options]);
            return $htmlFilename;
        }
        return $htmlFilename;
    }

    /**
     * @param string $cover
     *
     * @return string
     */
    private function createCover($cover)
    {
        $coverFilename = '';
        if ($cover) {
            $options = [ // cover page margins
                'margin-bottom' => '0mm',
                'margin-top' => '0mm',
                'margin-left' => '0mm',
                'margin-right' => '0mm'
            ];

            $coverPdf = $this->snappy->getOutputFromHtml($cover, $options);
            $coverFilename = $this->snappy->createTemporaryFile($coverPdf); // create cover page pdf

            $this->logger->debug(sprintf('Create pdf cover page at "%s"', $coverFilename), ['options' => $options]);
            return $coverFilename;
        }
        return $coverFilename;
    }

    /**
     * @param string $header
     */
    private function includeHeader($header)
    {
        if ($header) {
            $this->snappy->setOption('header-html', $header);
        }
    }

    /**
     * @param string $footer
     */
    private function includeFooter($footer)
    {
        if ($footer) {
            $this->snappy->setOption('footer-html', $footer);
        }
    }

    /**
     * Set options that apply to all pages
     */
    private function setDefaultOptions()
    {
        $this->snappy->setOption('background', true);
        $this->snappy->setOption('no-outline', true);
        $this->snappy->setOption('page-size', 'LETTER');
        $this->snappy->setOption('encoding', 'UTF-8');
    }

    /**
     * Set pdf passwords to combined document
     *
     * @param $ownerPassword
     * @param $userPassword
     * @param $filename
     *
     * @return mixed
     */
    private function setPassword($userPassword, $filename)
    {
        $this->logger->debug(sprintf('Set password for file "%s".', $filename));
        $pdf = new PDFTK($filename, ['useExec' => true]);
        $pdf->setUserPassword($userPassword);
        $pdf->passwordEncryption(128);
        $pdf->execute();

        $content = $pdf->toString();

        $this->snappy->removeTemporaryFiles(); // clear all temporary files
        $this->logger->debug(sprintf('Set password for file and clear temporary files "%s".', $filename));
        return $content;
    }
}