DocxMerge
=========

Simple library for merging multiple MS Word ".docx" files into one

Features
--------

+ Create valid docx for MS Office 2007 and above

Details
-------

+ For working with docx's ZIP I'm using [TbsZip](http://www.tinybutstrong.com/apps/tbszip/tbszip_help.html)

Example
------------

	require("DocxMerge.php");
	$dm = new DocxMerge();
	$dm->merge( [
        "templates/TplPage1.docx",
        "templates/TplPage2.docx"
    ], "/tmp/result.docx" );
