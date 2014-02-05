<?php
/**
 * Created by PhpStorm.
 * User: krustnic
 * Date: 05.02.14
 * Time: 11:59
 */

class Docx {

    // Path to current docx file
    private $docxPath;

    // Current _RELS data
    private $docxRels;
    // Current DOCUMENT data
    private $docxDocument;
    // Current CONTENT_TYPES data
    private $docxContentTypes;

    private $docxZip;

    private $RELS_ZIP_PATH          = "word/_rels/document.xml.rels";
    private $DOC_ZIP_PATH           = "word/document.xml";
    private $CONTENT_TYPES_PATH     = "[Content_Types].xml";
    private $ALT_CHUNK_TYPE         = "http://schemas.openxmlformats.org/officeDocument/2006/relationships/aFChunk";
    private $ALT_CHUNK_CONTENT_TYPE = "application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml";

    public function __construct( $docxPath ) {
        $this->docxPath = $docxPath;

        $this->docxZip = new TbsZip();
        $this->docxZip->Open( $this->docxPath );

        $this->docxRels = $this->readContent( $this->RELS_ZIP_PATH );
        $this->docxDocument = $this->readContent( $this->DOC_ZIP_PATH );
        $this->docxContentTypes = $this->readContent( $this->CONTENT_TYPES_PATH );
    }

    private function readContent( $zipPath ) {
        $content = $this->docxZip->FileRead( $zipPath );

        return $content;
    }

    private function writeContent( $content, $zipPath ) {
        $this->docxZip->FileReplace($zipPath, $content, TBSZIP_STRING);
        return 0;
    }

    public function addFile( $filePath, $zipName, $refID ) {
        $content = file_get_contents( $filePath );
        $this->docxZip->FileAdd( $zipName, $content );

        $this->addReference( $zipName, $refID );
        $this->addAltChunk( $refID );
        $this->addContentType( $zipName );
    }

    private function addReference( $zipName, $refID ) {
        $relXmlString = '<Relationship Target="../'.$zipName.'" Type="'.$this->ALT_CHUNK_TYPE.'" Id="'.$refID.'"/>';

        $p = strpos($this->docxRels, '</Relationships>');
        $this->docxRels = substr_replace($this->docxRels, $relXmlString, $p, 0);
    }

    private function addAltChunk( $refID ) {
        $xmlItem = '<w:altChunk r:id="'.$refID.'"/>';

        $p = strpos($this->docxDocument, '</w:body>');
        $this->docxDocument = substr_replace($this->docxDocument, $xmlItem, $p, 0);
    }

    private function addContentType( $zipName ) {
        $xmlItem = '<Override ContentType="'.$this->ALT_CHUNK_CONTENT_TYPE.'" PartName="/'.$zipName.'"/>';

        $p = strpos($this->docxContentTypes, '</Types>');
        $this->docxContentTypes = substr_replace($this->docxContentTypes, $xmlItem, $p, 0);
    }

    public function flush() {
        // Save RELS data
        $this->writeContent( $this->docxRels, $this->RELS_ZIP_PATH );
        // Save DOCUMENT data
        $this->writeContent( $this->docxDocument, $this->DOC_ZIP_PATH );
        // Save CONTENT TYPES data
        $this->writeContent( $this->docxContentTypes, $this->CONTENT_TYPES_PATH );

        // Save the merge into a third file
        // We cannot save to current file because it damage ZIP file
        $tempFile = tempnam( dirname( $this->docxPath ), "dm" );

        $this->docxZip->Flush(TBSZIP_FILE, $tempFile);

        // Replace current file with tempFile content
        rename( $tempFile, $this->docxPath );
    }

} 