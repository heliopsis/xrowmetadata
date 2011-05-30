﻿<?php

class xrowMetaDataType extends eZDataType
{
    const DATA_TYPE_STRING = 'xrowmetadata';

    /*!
     Initializes with a keyword id and a description.
    */
    function xrowMetaDataType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Metadata', 'Datatype name' ), array(
            'serialize_supported' => true
        ) );
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $originalContentObjectAttributeID = $originalContentObjectAttribute->attribute( 'id' );
            $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );

            // if translating or copying an object
            if ( $originalContentObjectAttributeID != $contentObjectAttributeID )
            {
                $metadata = $originalContentObjectAttribute->content();
                if ( $metadata instanceof xrowMetadata )
                {
                    //@TODO do something to store the stuff
                }
            }
        }
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_xrowmetadata_data_array_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_xrowmetadata_data_array_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute = $contentObjectAttribute->contentClassAttribute();
            if ( ! $classAttribute->attribute( 'is_information_collector' ) and $contentObjectAttribute->validateIsRequired() )
            {
                if ( $data == "" )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
                if ( empty( $data['title'] ) )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Title required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

        }
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var keyword input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_xrowmetadata_data_array_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_xrowmetadata_data_array_' . $contentObjectAttribute->attribute( 'id' ) );
            $meta = self::fillMetaData( $data );
            $contentObjectAttribute->setContent( $meta );
            return true;
        }
        return false;
    }
    function onPublish( $contentObjectAttribute, $contentObject, $publishedNodes )
    {
        $trans = $contentObjectAttribute->fetchAttributeTranslations();
        $xml = $contentObjectAttribute->attribute( "data_text" );
        $dom2 = new DOMDocument( '1.0', 'utf-8' );
        $dom2->loadXML( $xml );
        $priority = $dom2->getElementsByTagName( "priority" )->item( 0 );
        $change = $dom2->getElementsByTagName( "change" )->item( 0 );
        $googlemap = $dom2->getElementsByTagName( "googlemap" )->item( 0 );
        foreach ( $trans as $translation )
        {
            if ( $contentObjectAttribute->LanguageCode == $translation->LanguageCode )
            {
                continue;
            }
            $old = $translation->attribute( "data_text" );
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $dom->loadXML( $old );

            $dom->documentElement->replaceChild( $dom->importNode( $priority, true ), $dom->getElementsByTagName( "priority" )->item( 0 ) );
            $dom->documentElement->replaceChild( $dom->importNode( $change, true ), $dom->getElementsByTagName( "change" )->item( 0 ) );
            $dom->documentElement->replaceChild( $dom->importNode( $googlemap, true ), $dom->getElementsByTagName( "googlemap" )->item( 0 ) );

            $translation->setAttribute( "data_text", $dom->saveXML() );
            eZPersistentObject::storeObject( $translation );
        }
    }
    /*!
     Does nothing since it uses the data_text field in the content object attribute.
     See fetchObjectAttributeHTTPInput for the actual storing.
    */
    function storeObjectAttribute( $attribute )
    {
    	if( $attribute->ID === null )
    	{
    		eZPersistentObject::storeObject( $attribute );
    	}

        $meta= $attribute->content();
        $xml = new DOMDocument( "1.0", "UTF-8" );
        $xmldom = $xml->createElement( "MetaData" );
        $node = $xml->createElement( "title", htmlspecialchars( $meta->title, ENT_QUOTES, 'UTF-8' ) );
        $xmldom->appendChild( $node );
        $node = $xml->createElement( "keywords", htmlspecialchars( $meta->keywords, ENT_QUOTES, 'UTF-8' ) );
        $xmldom->appendChild( $node );
        $node = $xml->createElement( "description", htmlspecialchars( $meta->description, ENT_QUOTES, 'UTF-8' ) );
        $xmldom->appendChild( $node );
        $node = $xml->createElement( "priority", htmlspecialchars( $meta->priority, ENT_QUOTES, 'UTF-8' ) );
        $xmldom->appendChild( $node );
        $node = $xml->createElement( "change", htmlspecialchars( $meta->change, ENT_QUOTES, 'UTF-8' ) );
        $xmldom->appendChild( $node );
        $node = $xml->createElement( "googlemap", htmlspecialchars( $meta->googlemap, ENT_QUOTES, 'UTF-8' ) );
        $xmldom->appendChild( $node );
        $xml->appendChild( $xmldom );
        $attribute->setAttribute( 'data_text', $xml->saveXML() );

        // save keywords
        $keyword = new eZKeyword();
        $keyword->initializeKeyword( $meta->keywords );
        $keyword->store( $attribute );
    }

    /*!
     Delete stored object attribute
    */
    function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        if ( $version != null ) // Do not delete if discarding draft
        {
            return;
        }

        $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );

        $db = eZDB::instance();

        /* First we retrieve all the keyword ID related to this object attribute */
        $res = $db->arrayQuery( "SELECT keyword_id
                                 FROM ezkeyword_attribute_link
                                 WHERE objectattribute_id='$contentObjectAttributeID'" );
        if ( !count ( $res ) )
        {
            /* If there are no keywords at all, we abort the function as there
             * is nothing more to do */
            return;
        }
        $keywordIDs = array();
        foreach ( $res as $record )
            $keywordIDs[] = $record['keyword_id'];
        $keywordIDString = implode( ', ', $keywordIDs );

        /* Then we see which ones only have a count of 1 */
        $res = $db->arrayQuery( "SELECT keyword_id
                                 FROM ezkeyword, ezkeyword_attribute_link
                                 WHERE ezkeyword.id = ezkeyword_attribute_link.keyword_id
                                     AND ezkeyword.id IN ($keywordIDString)
                                 GROUP BY keyword_id
                                 HAVING COUNT(*) = 1" );
        $unusedKeywordIDs = array();
        foreach ( $res as $record )
            $unusedKeywordIDs[] = $record['keyword_id'];
        $unusedKeywordIDString = implode( ', ', $unusedKeywordIDs );

        /* Then we delete those unused keywords */
        if ( $unusedKeywordIDString )
            $db->query( "DELETE FROM ezkeyword WHERE id IN ($unusedKeywordIDString)" );

        /* And as last we remove the link between the keyword and the object
         * attribute to be removed */
        $db->query( "DELETE FROM ezkeyword_attribute_link
                     WHERE objectattribute_id='$contentObjectAttributeID'" );
    }

    /*!
     \reimp
    */
    function validateClassAttributeHTTPInput( $http, $base, $attribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     \reimp
    */
    function fixupClassAttributeHTTPInput( $http, $base, $attribute )
    {
    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( $http, $base, $attribute )
    {
        return true;
    }
    /*
     * @return xrowMetaData
     */
    function fetchMetaData( $attribute )
    {
        try
        {
           $xml = new SimpleXMLElement( $attribute->attribute( 'data_text' ) );
           $meta = new xrowMetaData( htmlspecialchars_decode( (string)$xml->title, ENT_QUOTES ),
                                     htmlspecialchars_decode( (string)$xml->keywords, ENT_QUOTES ),
                                     htmlspecialchars_decode( (string)$xml->description, ENT_QUOTES ),
                                     htmlspecialchars_decode( (string)$xml->priority, ENT_QUOTES ),
                                     htmlspecialchars_decode( (string)$xml->change, ENT_QUOTES ),
                                     htmlspecialchars_decode( (string)$xml->googlemap , ENT_QUOTES ) );
           return $meta;
        }
        catch ( Exception $e )
        {
            return new xrowMetaData();
        }


    }
    /*
     * @return xrowMetaData
     */
    function fillMetaData( $array )
    {
        return new xrowMetaData( $array['title'], $array['keywords'], $array['description'], $array['priority'], $array['change'], $array['googlemap'] );
    }
    /*!
     Returns the content.
    */
    function objectAttributeContent( $attribute )
    {
        return self::fetchMetaData( $attribute );
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $attribute )
    {
        $meta = self::fetchMetaData( $attribute );
        return $meta->title .' '. $meta->keywords.' '. $meta->description;
    }

    /*!
     \reuturn the collect information action if enabled
    */
    function contentActionList( $classAttribute )
    {
        return array();
    }

    /*!
     Returns the content of the keyword for use as a title
    */
    function title( $attribute, $name = null )
    {
        $meta = self::fetchMetaData( $attribute );
        return $meta->title;
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $meta = self::fetchMetaData( $contentObjectAttribute );
        if ( $meta instanceof xrowMetaData ) {
            return true;
        }
        else
        {
            return false;
        }
    }

    /*!
     \reimp
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        if ( $string != '' )
        {
            $contentObjectAttribute->setAttribute( 'data_text', $string );
            $meta = self::fetchMetaData( $contentObjectAttribute );
            $contentObjectAttribute->setContent( $meta );
        }
        return true;
    }

    /*!
     \reimp
     \param package
     \param content attribute

     \return a DOM representation of the content object attribute
    */
    function serializeContentObjectAttribute( $package, $objectAttribute )
    {
        /*
         * @TODO do it later
        $node = $this->createContentObjectAttributeDOMNode( $objectAttribute );

        $keyword = new xrowMetaData( );
        $keyword->fetch( $objectAttribute );
        $keyWordString = $keyword->keywordString();
        $dom = $node->ownerDocument;
        $keywordStringNode = $dom->createElement( 'keyword-string', $keyWordString );
        $node->appendChild( $keywordStringNode );

        return $node;
        */
    }

    /*!
     \reimp
     Unserailize contentobject attribute

     \param package
     \param contentobject attribute object
     \param domnode object
    */
    function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
               /*
         * @TODO do it later
        $keyWordString = $attributeNode->getElementsByTagName( 'keyword-string' )->item( 0 )->textContent;
        $keyword = new xrowMetaData( );
        $keyword->initializeKeyword( $keyWordString );
        $objectAttribute->setContent( $keyword );
        */
    }
}

eZDataType::register( xrowMetaDataType::DATA_TYPE_STRING, 'xrowMetaDataType' );

?>
