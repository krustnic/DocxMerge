<?php

namespace DocxMerge\DocxMerge;

    class Prettify {

        public function test( $str ) {
            //echo $this->indexOfN( $str, '$', 2 );
            $this->removeTags( $str );
        }

        private function indexOfN( $str, $needle, $number ) {
            if ( substr_count($str, $needle) < $number ) return FALSE;

            $startPos = 0;
            $result   = 0;

            for( $i=0; $i<$number; $i++ ) {
                $idx = strpos( substr( $str, $startPos ), $needle );
                $result = $startPos + $idx;

                $startPos = $result + 1;
            }

            return $result;
        }

        private function findAndReplacePlaceholderWithTags( $str, $idx ) {
            $bracketIdx = strpos( $str, '{', $idx );
            $space      = substr( $str, $idx + 1, $bracketIdx - $idx - 1 );

            //if ( $space != "" && simplexml_load_string( $space, 'SimpleXMLElement', LIBXML_NOERROR  ) === FALSE ) return FALSE;
            if ( $space != "" && strip_tags( $space ) != "" ) return FALSE;

            $str = substr_replace( $str, '', $idx + 1, $bracketIdx - $idx - 1 );

            // Refresh bracket index after tring update
            $bracketIdx = strpos( $str, '{', $idx );

            $endBracketIdx = strpos( $str, '}', $bracketIdx );
            $space         = substr( $str, $bracketIdx + 1, $endBracketIdx - $bracketIdx - 1 );

            $placeholderName = strip_tags( $space );

            $str = substr_replace( $str, $placeholderName, $bracketIdx + 1, $endBracketIdx - $bracketIdx - 1 );

            return $str;
        }

        // Remove all tags between placeholders (occure when you delete/backspace in editor)
        public function removeTags( $str ) {
            $placeholderCandidates = substr_count($str, '$');
            $lastIdx = -1;

            for($i=0; $i<$placeholderCandidates; $i++) {
                $idx = $this->indexOfN( $str, '$', $i + 1 );

                $status = $this->findAndReplacePlaceholderWithTags( $str, $idx );

                if ( $status !== FALSE ) {
                    $str = $status;
                }

            }

            return $str;
        }



    }