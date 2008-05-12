<?php
/**
 * File containing the ezcDocumentRstDirective class
 *
 * @package Document
 * @version //autogen//
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Decoration handler for RST image directives
 * 
 * @package Document
 * @version //autogen//
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
class ezcDocumentRstFigureDirective extends ezcDocumentRstImageDirective
{
    /**
     * Transform directive to docbook
     *
     * Create a docbook XML structure at the directives position in the
     * document.
     * 
     * @param DOMDocument $document 
     * @param DOMElement $root 
     * @return void
     */
    public function toDocbook( DOMDocument $document, DOMElement $root )
    {
        parent::toDocbook( $document, $root );

        $text = '';
        foreach ( $this->node->nodes as $node )
        {
            $text .= $node->token->content;
        }
        $text = trim( $text );

        if ( !empty( $text ) )
        {
            $media = $root->getElementsBytagName( 'mediaobject' )->item( 0 );
            $caption = $document->createElement( 'caption' );
            $media->appendChild( $caption );

            $paragraph = $document->createElement( 'para', htmlspecialchars( $text ) );
            $caption->appendChild( $paragraph );
        }
    }
}

?>
