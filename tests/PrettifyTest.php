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
                '<w:t>${</w:t></w:r><w:r w:rsidR="0056434F" w:rsidRPr="00377F0A"><w:t>ДАТА_РОЖДЕНИЯ</w:t></w:r><w:r w:rsidR="00214373" w:rsidRPr="00377F0A"><w:t>}</w:t>' => '<w:t>${ДАТА_РОЖДЕНИЯ}</w:t>'
            ];
            $prettify = new Prettify();
            foreach($placeholders as $tested => $expected){
                $actual = $prettify->removeTags($tested);
                $this->assertEquals($expected, $actual, "tested string is '$tested'");
            }
        }



    }