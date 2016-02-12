DocxMerge
=========

Simple library for merging multiple MS Word ".docx" files into one

Features
--------

+ Merge docx files generating valid docx for MS Office 2007 and above
+ Add a page break between merged documents

Details
-------

+ For working with docx's ZIP I'm using [TbsZip](http://www.tinybutstrong.com/apps/tbszip/tbszip_help.html)

Merge Example
-------------

	require("DocxMerge.php");
	$dm = new DocxMerge();
	$dm->merge( [
        "templates/TplPage1.docx",
        "templates/TplPage2.docx"
    ], "/tmp/result.docx", true );


setValues Example
-----------------

	# Use "${NAME}" in docx file to create placeholders

	require("DocxMerge.php");
	$dm = new DocxMerge();
	$dm->setValues( "templates/template.docx",
                    "templates/result.docx",
    				array( "NAME" => "Sterling", "SURNAME" => "Archer" ) );
