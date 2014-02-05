DocxMerge
=========

Simple library for merging multiple MS Word ".docx" files into one

Features
--------

+ Create valid docx for MS Office 2007 and above

Example
------------

	require("DocxMerge.php");
	$dm = new DocxMerge();
	$dm->merge( [
        "templates/TplPage1.docx",
        "templates/TplPage2.docx"
    ], "/tmp/result.docx" );
