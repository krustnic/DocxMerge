DocxMerge
=========

Simple library for merging multiple MS Word ".docx" files into one

Features
--------

+ Create valid docx for MS Office 2007 and above

Details
-------

+ For working with docx's ZIP I'm using [TbsZip](http://www.tinybutstrong.com/apps/tbszip/tbszip_help.html)

Install
-------

    php composer.phar require krustnic/docx-merge

Merge Example
-------------

    require "vendor/autoload.php";
    use DocxMerge\DocxMerge;
    
	$dm = new DocxMerge();
	$dm->merge( [
        "templates/TplPage1.docx",
        "templates/TplPage2.docx"
    ], "/tmp/result.docx" );


setValues Example
-----------------

	# Use "${NAME}" in docx file to create placeholders

	require "vendor/autoload.php";
    use DocxMerge\DocxMerge;
    
	$dm = new DocxMerge();
	$dm->setValues( "templates/template.docx",
                    "templates/result.docx",
    				array( "NAME" => "Sterling", "SURNAME" => "Archer" ) );
    				
Or with styles ("bold", "italic", "underline"):
    
    $dm->setValues( "templates/template.docx",
                    "templates/result.docx",
                    [ 
        			    "NAME" => [
        			        [
                                "value"      => "Sterling",
                                "decoration" => [ "bold", "italic" ]
        			        ],
        			        [
                                "value"      => "Archer",
                                "decoration" => [ "bold", "underline" ] 
        			        ]
        			    ]
                    ]);