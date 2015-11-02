<?php

    use DocxMerge\DocxMerge;

    class DocxMergeTest extends PHPUnit_Framework_TestCase
    {

        public function test_setValues()
        {
            $doc = new DocxMerge();
            $doc->setValues(__DIR__.'\test_src\test_doc.docx', __DIR__.'\test_result\test_'.time().'.docx', ['name'=>'test_name']);
        }
    }