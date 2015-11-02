<?php

    use DocxMerge\DocxMerge\Prettify;

    class PrettifyTest extends PHPUnit_Framework_TestCase
    {

        public function testPrettify_removeTags_success()
        {
            $placeholders = [
                '${placeholder}' => '${placeholder}',
                'some_text ${place<w attr="value"></w><w></w><w/>holder}some_text<w></w><w/>' => 'some_text ${placeholder}some_text<w></w><w/>',
                '$<w></w>jkl{placeholder}' => '$<w></w>jkl{placeholder}'
            ];
            $prettify = new Prettify();
            foreach($placeholders as $tested => $expected){
                $actual = $prettify->removeTags($tested);
                $this->assertEquals($expected, $actual, "tested string is '$tested'");
            }
        }



    }