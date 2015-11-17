<?php

    use DocxMerge\DocxMerge\Prettify;

    class PrettifyTest extends PHPUnit_Framework_TestCase
    {

        public function testPrettify_removeTags_success()
        {
            $placeholders = [
                '${placeholder}' => '${placeholder}',
                'some_text ${place<w attr="value"></w><w></w><w/>holder}some_text<w></w><w/>' => 'some_text ${placeholder}some_text<w></w><w/>',
                '$<w></w>jkl{placeholder}' => '$<w></w>jkl{placeholder}',
                '<w:t>${</w:t></w:r><w:r w:rsidR="0056434F" w:rsidRPr="00377F0A"><w:t>ДАТА_РОЖДЕНИЯ</w:t></w:r><w:r w:rsidR="00214373" w:rsidRPr="00377F0A"><w:t>}</w:t>' => '<w:t>${ДАТА_РОЖДЕНИЯ}</w:t>',
                '<w:t>$</w:t></w:r><w:proofErr w:type="gramEnd"/><w:r w:rsidR="00702E2B" w:rsidRPr="00702E2B"><w:rPr><w:sz w:val="28"/></w:rPr><w:t>{ДАТА_РОЖДЕНИЯ}' => '<w:t>${ДАТА_РОЖДЕНИЯ}',
                '$</w:t></w:r><w:r w:rsidR="00D50786" w:rsidRPr="00C664E2"><w:rPr><w:noProof/></w:rPr><w:t>{</w:t></w:r><w:r w:rsidR="00D50786"><w:rPr><w:noProof/></w:rPr><w:t>РАЙОН_ОБЩЕЖИТИЯ</w:t></w:r><w:r w:rsidR="00D50786" w:rsidRPr="00C664E2"><w:rPr><w:noProof/></w:rPr><w:t>}' => '${РАЙОН_ОБЩЕЖИТИЯ}'
            ];
            $prettify = new Prettify();
            foreach($placeholders as $tested => $expected){
                $actual = $prettify->removeTags($tested);
                $this->assertEquals($expected, $actual, "tested string is '$tested'");
            }
        }



    }