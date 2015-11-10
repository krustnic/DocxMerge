<?php
/**
 * User: krustnic
 * Date: 04.02.14
 * Time: 11:17
 */
namespace DocxMerge;

use DocxMerge\DocxMerge\Docx;

class DocxMerge {

    /**
     * Merge files in $docxFilesArray order and
     * create new file $outDocxFilePath
     * @param $docxFilesArray
     * @param $outDocxFilePath
     * @return int
     */
    public function merge( $docxFilesArray, $outDocxFilePath ) {
        if ( count($docxFilesArray) == 0 ) {
            // No files to merge
            return -1;
        }

        if ( substr( $outDocxFilePath, -5 ) != ".docx" ) {
            $outDocxFilePath .= ".docx";
        }

        if ( !copy( $docxFilesArray[0], $outDocxFilePath ) ) {
            // Cannot create file
            return -2;
        }

        $docx = new Docx( $outDocxFilePath );
        for( $i=1; $i<count( $docxFilesArray ); $i++ ) {
            $docx->addFile( $docxFilesArray[$i], "part".$i.".docx", "rId10".$i );
        }

        $docx->flush();

        return 0;
    }

    public function setValues( $templateFilePath, $outputFilePath, $data ) {
        if ( !file_exists( $templateFilePath ) ) {
            return -1;
        }

        if ( !copy( $templateFilePath, $outputFilePath ) ) {
            // Cannot create output file
            return -2;
        }

        $docx = new Docx( $outputFilePath );
        $docx->prepare();
        $docx->loadHeadersAndFooters();
        foreach( $data as $key => $value ) {
            $docx->findAndReplace( "\${".$key."}", $value );
        }

        $docx->flush();
    }

}