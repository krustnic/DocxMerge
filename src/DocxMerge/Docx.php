<?php
/**
 * Created by PhpStorm.
 * User: krustnic
 * Date: 05.02.14
 * Time: 11:59
 */

namespace DocxMerge\DocxMerge;

use DocxMerge\libraries\TbsZip;

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

    // Array "zip path" => "content"
    private $headerAndFootersArray = [];

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

    public function loadHeadersAndFooters() {
        $relsXML = new \SimpleXMLElement( $this->docxRels );
        foreach( $relsXML as $rel ) {
            if ( $rel["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" ||
                 $rel["Type"] == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" ) {
                $path = "word/".$rel["Target"];
                $this->headerAndFootersArray[ $path ] = $this->readContent( $path );
            }
        }
    }

    /* Add styles for text */

    private function findAndReplaceWithStyles( $key, $value ) {
        $lastPos = 0;
        $positions = array();

        while (($lastPos = strpos($this->docxDocument, $key, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($key);
        }

        foreach ($positions as $position) {
            $wrStartPosition1 = strrpos( substr( $this->docxDocument, 0, $position ), "<w:r " );
            $wrStartPosition2 = strrpos( substr( $this->docxDocument, 0, $position ), "<w:r>" );

            if ( $wrStartPosition1 === FALSE && $wrStartPosition2 === FALSE ) continue;
            if ( $wrStartPosition1 === FALSE ) $wrStartPosition = $wrStartPosition2;
            if ( $wrStartPosition2 === FALSE ) $wrStartPosition = $wrStartPosition1;
            if ( $wrStartPosition1 !== FALSE && $wrStartPosition2 !== FALSE ) {
                $wrStartPosition = max( $wrStartPosition1, $wrStartPosition2 );
            }

            $wrStopPosition    = strpos( substr( $this->docxDocument, $position ), "</w:r>" ) + $position + 6;

            // Use placeholder w:r id-s
            $wrTagStopPosition = strpos( substr( $this->docxDocument, $wrStartPosition ), ">" ) + $wrStartPosition + 1;
            $wrTag = substr( $this->docxDocument, $wrStartPosition, $wrTagStopPosition - $wrStartPosition );

            // Use placeholder styles
            $wPrStartPosition  = strpos( substr( $this->docxDocument, $wrStartPosition ), "<w:rPr" ) + $wrStartPosition;
            $wPrStopPosition   = strpos( substr( $this->docxDocument, $wPrStartPosition ), "</w:rPr>" ) + $wPrStartPosition;
            $wPrTag = substr( $this->docxDocument, $wPrStartPosition, $wPrStopPosition - $wPrStartPosition );

            $insertString = "";
            $idx = 0;
            $len = count( $value );
            foreach( $value as $word ) {
                $wPrStyles = $wPrTag;
                foreach( $word["decoration"] as $style ) {
                    if ( $style == "bold" ) {
                        $wPrStyles .= "<w:b/>";
                    }

                    if ( $style == "italic" ) {
                        $wPrStyles .= "<w:i />";
                    }

                    if ( $style == "underline" ) {
                        $wPrStyles .= '<w:u w:val="single"/>';
                    }
                }
                $wPrStyles .= "</w:rPr>";

                $insertPart = $wrTag.$wPrStyles.'<w:t xml:space="preserve">'.$word["value"].'</w:t></w:r>';
                $insertString .= $insertPart;

                // Add space between words, except last
                if ( $idx != $len - 1 ) {
                    $insertString .= '<w:r><w:t xml:space="preserve"> </w:t></w:r>';
                }

                $idx += 1;
            }

            $this->docxDocument = substr( $this->docxDocument, 0, $wrStartPosition ).$insertString.substr( $this->docxDocument, $wrStopPosition);
        }
    }

    /* end */

    public function findAndReplace( $key, $value ) {
        // Apply styles
        if ( is_array( $value ) ) {
            $this->findAndReplaceWithStyles($key, $value);
            return;
        }

        // Search/Replace in document
        $this->docxDocument = str_replace( $key, $value, $this->docxDocument );
        // Search/Replace in footers and headers
        foreach( $this->headerAndFootersArray as $path => $content ) {
            $this->headerAndFootersArray[$path] = str_replace( $key, $value, $content );
        }
    }

    public function findAndReplaceFirst( $key, $value ) {
        // Search/Replace first key in document                
        if ( strpos( $this->docxDocument, $key ) === FALSE ) return;
        if ( strpos( $this->docxDocument, $key ) + strlen( $key ) === FALSE ) return;

        $leftPart  = substr( $this->docxDocument, 0, strpos( $this->docxDocument, $key ) );
        $rightPart = substr( $this->docxDocument, strpos( $this->docxDocument, $key ) + strlen( $key ) );
        $this->docxDocument = $leftPart.$value.$rightPart;
    }

    public function flush() {
        // Save RELS data
        $this->writeContent( $this->docxRels, $this->RELS_ZIP_PATH );
        // Save DOCUMENT data
        $this->writeContent( $this->docxDocument, $this->DOC_ZIP_PATH );
        // Save CONTENT TYPES data
        $this->writeContent( $this->docxContentTypes, $this->CONTENT_TYPES_PATH );
        // Save footers and headers
        foreach( $this->headerAndFootersArray as $path => $content ) {
            $this->writeContent( $content, $path );
        }

        // Save the merge into a third file
        // We cannot save to current file because it damage ZIP file
        $tempFile = tempnam( dirname( $this->docxPath ), "dm" );

        $this->docxZip->Flush(TBSZIP_FILE, $tempFile);

        // Replace current file with tempFile content
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            copy( $tempFile, $this->docxPath );
        }
        else {
            rename($tempFile, $this->docxPath);
        }
    }

    public function copyRowWithPlaceholder( $placeholder, $N ) {
        $needle = "\${".$placeholder."}";

        $pos = strpos( $this->docxDocument, $needle );
        if ( $pos !== FALSE ) {
            // find tables's tr
            $trBeginPos = strrpos( substr( $this->docxDocument, 0, $pos ), "<w:tr " );

            if ( $trBeginPos === FALSE ) {
                return;
            }

            $trEndPos = strpos( substr( $this->docxDocument, $pos ), "</w:tr>" );

            if ( $trEndPos === FALSE ) {
                return;
            }

            $trEndPos += $pos + 7;

            $trBody = substr( $this->docxDocument, $trBeginPos, $trEndPos - $trBeginPos );
            $result = "";

            for( $i=0; $i<$N; $i++ ) {
                $result .= $trBody;
            }

            // insert new rows
            $this->docxDocument = substr_replace( $this->docxDocument, $result, $trEndPos, 0 );
        }        
    }

    public function prepare() {
        $prettify = new Prettify();
        $this->docxDocument = $prettify->removeTags( $this->docxDocument );
    }

} 
