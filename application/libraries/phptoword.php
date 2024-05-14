<?php 

    // require_once 'bootstrap.php';
    require_once 'vendor/autoload.php';

    use PhpOffice\PhpWord\Shared\Converter;

    class Phptoword 
    {

        function contoh_phptoword() 
        {

            // Creating the new document...
            $phpWord = new \PhpOffice\PhpWord\PhpWord();

            /* Note: any element you append to a document must reside inside of a Section. */

            // Adding an empty Section to the document...
            $section = $phpWord->addSection();
            // Adding Text element to the Section having font styled by default...
            $section->addText(
                '"Learn from yesterday, live for today, hope for tomorrow. '
                    . 'The important thing is not to stop questioning." '
                    . '(Albert Einstein)'
            );

            /*
            * Note: it's possible to customize font style of the Text element you add in three ways:
            * - inline;
            * - using named font style (new font style object will be implicitly created);
            * - using explicitly created font style object.
            */

            // Adding Text element with font customized inline...
            $section->addText(
                '"Great achievement is usually born of great sacrifice, '
                    . 'and is never the result of selfishness." '
                    . '(Napoleon Hill)',
                array('name' => 'Tahoma', 'size' => 10)
            );

            // Adding Text element with font customized using named font style...
            $fontStyleName = 'oneUserDefinedStyle';
            $phpWord->addFontStyle(
                $fontStyleName,
                array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
            );
            $section->addText(
                '"The greatest accomplishment is not in never falling, '
                    . 'but in rising again after you fall." '
                    . '(Vince Lombardi)',
                $fontStyleName
            );

            // Adding Text element with font customized using explicitly created font style object...
            $fontStyle = new \PhpOffice\PhpWord\Style\Font();
            $fontStyle->setBold(true);
            $fontStyle->setName('Tahoma');
            $fontStyle->setSize(13);
            $myTextElement = $section->addText('"Believe you can and you\'re halfway there." (Theodor Roosevelt)');
            $myTextElement->setFontStyle($fontStyle);

            // Saving the document as OOXML file...
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('result_phptoword/helloWorld.docx');

            // Saving the document as ODF file...
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
            $objWriter->save('result_phptoword/helloWorld.odt');

            // Saving the document as HTML file...
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $objWriter->save('result_phptoword/helloWorld.html');

            /* Note: we skip RTF, because it's not XML-based and requires a different example. */
            /* Note: we skip PDF, because "HTML-to-PDF" approach is used to create PDF documents. */

        }

        function contoh_phptoword_2()
        {

            // Creating the new document...
            $phpWord = new \PhpOffice\PhpWord\PhpWord();

            /* Note: any element you append to a document must reside inside of a Section. */

            // Adding an empty Section to the document...
            $section = $phpWord->addSection();
            // Adding Text element to the Section having font styled by default...
            $section->addText(
                '"Learn from yesterday, live for today, hope for tomorrow. '
                    . 'The important thing is not to stop questioning." '
                    . '(Albert Einstein)'
            );

            /*
            * Note: it's possible to customize font style of the Text element you add in three ways:
            * - inline;
            * - using named font style (new font style object will be implicitly created);
            * - using explicitly created font style object.
            */

            // Adding Text element with font customized inline...
            $section->addText(
                '"Great achievement is usually born of great sacrifice, '
                    . 'and is never the result of selfishness." '
                    . '(Napoleon Hill)',
                array('name' => 'Tahoma', 'size' => 10)
            );

            // Adding Text element with font customized using named font style...
            $fontStyleName = 'oneUserDefinedStyle';
            $phpWord->addFontStyle(
                $fontStyleName,
                array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
            );
            $section->addText(
                '"The greatest accomplishment is not in never falling, '
                    . 'but in rising again after you fall." '
                    . '(Vince Lombardi)',
                $fontStyleName
            );

            // Adding Text element with font customized using explicitly created font style object...
            $fontStyle = new \PhpOffice\PhpWord\Style\Font();
            $fontStyle->setBold(true);
            $fontStyle->setName('Tahoma');
            $fontStyle->setSize(13);
            $myTextElement = $section->addText('"Believe you can and you\'re halfway there." (Theodor Roosevelt)');
            $myTextElement->setFontStyle($fontStyle);

            //If you want to structure your document or build table of contents, 
            //you need titles or headings. To add a title to the document, 
            //use the addTitleStyle and addTitle method. 
            //If depth is 0, a Title will be inserted, otherwise a Heading1, Heading2, …
            $phpWord->addTitleStyle(1, array('name' => 'arial', 'size' => 22, 'bold' => true), array('alignment' => 'center'));
            $section->addTitle('This is Title', 1);

            //Text can be added by using addtext and addTextRun. 
            //addText is used for creating simple paragraphs that only contain texts with the same style.
            //addTextRun is used for creating complex paragraphs that contain text with different style 
            //(some bold, other italics, etc) or other elements, e.g. images or links.
            $section->addText(
                '"The greatest accomplishment is not in never falling, but in rising again after you fall." (Vince Lombardi)', 
                array('name' => 'arial', 'size' => 12), 
                array('alignment' => 'both')
            );
            $textrun = $section->addTextRun(array('alignment' => 'right'));

            //You can add Hyperlinks to the document by using the function addLink:
            $section->addLink('http://peppd.bappenas.go.id', 'BAPPENAS', array('name' => 'arial', 'size' => 12, 'underline' => 'single', 'color' => '#0000FF'), array('alignment' => 'left'));

            //To add tables, rows, and cells, use the addTable, addRow, adn addCell method
            $fancyTableStyle = array('borderSize' => 6, 'borderColor' => '999999');
            $cellRowSpan = array('vMerge' => 'restart', 'valign' => 'center', 'bgColor' => 'FFFF00');
            $cellRowContinue = array('vMerge' => 'continue');
            $cellColSpan = array('gridSpan' => 2, 'valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellVCentered = array('valign' => 'center');

            $spanTableStyleName = 'Colspan Rowspan';
            $phpWord->addTableStyle($spanTableStyleName, $fancyTableStyle);
            $table = $section->addTable($spanTableStyleName);

            $table->addRow();

            $cell1 = $table->addCell(2000, $cellRowSpan);
            $textrun1 = $cell1->addTextRun($cellHCentered);
            $textrun1->addText('A');
            $textrun1->addFootnote()->addText('Row span');

            $cell2 = $table->addCell(4000, $cellColSpan);
            $textrun2 = $cell2->addTextRun($cellHCentered);
            $textrun2->addText('B');
            $textrun2->addFootnote()->addText('Column span');
            $table->addCell(2000, $cellRowSpan)->addText('E', null, $cellHCentered);

            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            $table->addCell(2000, $cellVCentered)->addText('C', null, $cellHCentered);
            $table->addCell(2000, $cellVCentered)->addText('D', null, $cellHCentered);
            $table->addCell(null, $cellRowContinue);

            //Charts can be added using
            $categories = array('A', 'B', 'C', 'D', 'E');
            $series = array(1, 3, 2, 5, 4);
            $style = array(
                'width'          => Converter::cmToEmu(5),
                'height'         => Converter::cmToEmu(4),
                '3d'             => true,
                'showAxisLabels' => false,
                'showGridX'      => false,
                'showGridY'      => false,
            );
            $chart = $section->addChart('line', $categories, $series, $style);

            // 2D charts

            $section = $phpWord->addSection();
            $section->addTitle('2D charts', 1);
            $section = $phpWord->addSection(array('colsNum' => 2, 'breakType' => 'continuous'));

            $chartTypes = array('pie', 'doughnut', 'bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $twoSeries = array('bar', 'column', 'line', 'area', 'scatter', 'radar', 'stacked_bar', 'percent_stacked_bar', 'stacked_column', 'percent_stacked_column');
            $threeSeries = array('bar', 'line');
            $categories = array('A', 'B', 'C', 'D', 'E');
            $series1 = array(8, 8, 8, 8, 8);
            $series2 = array(3, 1, 7, 2, 6);
            $series3 = array(8, 3, 2, 5, 4);
            $showGridLines = false;
            $showAxisLabels = false;

            foreach ($chartTypes as $chartType) {

                $section->addTitle(ucfirst($chartType), 2);
                $chart = $section->addChart($chartType, $categories, $series1);
                $chart->getStyle()->setWidth(Converter::inchToEmu(2.5))->setHeight(Converter::inchToEmu(2));
                $chart->getStyle()->setShowGridX($showGridLines);
                $chart->getStyle()->setShowGridY($showGridLines);
                $chart->getStyle()->setShowAxisLabels($showAxisLabels);

                if (in_array($chartType, $twoSeries)) {
                    $chart->addSeries($categories, $series2);
                }

                if (in_array($chartType, $threeSeries)) {
                    $chart->addSeries($categories, $series3);
                }

                $section->addTextBreak();
            }

            // 3D charts
            $section = $phpWord->addSection(array('breakType' => 'continuous'));
            $section->addTitle('3D charts', 1);
            $section = $phpWord->addSection(array('colsNum' => 2, 'breakType' => 'continuous'));

            $chartTypes = array('pie', 'bar', 'column', 'line', 'area');
            $multiSeries = array('bar', 'column', 'line', 'area');

            $style = array(
                'width'          => Converter::cmToEmu(5),
                'height'         => Converter::cmToEmu(4),
                '3d'             => true,
                'showAxisLabels' => $showAxisLabels,
                'showGridX'      => $showGridLines,
                'showGridY'      => $showGridLines,
            );

            foreach ($chartTypes as $chartType) {
                $section->addTitle(ucfirst($chartType), 2);
                $chart = $section->addChart($chartType, $categories, $series1, $style);
                
                if (in_array($chartType, $multiSeries)) {
                    $chart->addSeries($categories, $series2);
                    $chart->addSeries($categories, $series3);
                }

                $section->addTextBreak();
            }

            // Saving the document as OOXML file...
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('result_phptoword/helloChart.docx');

            // Saving the document as ODF file...
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
            $objWriter->save('result_phptoword/helloChart.odt');

            // Saving the document as HTML file...
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $objWriter->save('result_phptoword/helloChart.html');

            /* Note: we skip RTF, because it's not XML-based and requires a different example. */
            /* Note: we skip PDF, because "HTML-to-PDF" approach is used to create PDF documents. */

        }

    }

?>