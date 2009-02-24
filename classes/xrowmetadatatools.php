<?php

class xrowMetaDataFunctions
{

    function fetchByObject( eZContentObject $object )
    {
        $attributes = $object->fetchDataMap();
        foreach ( $attributes as $attribute )
        {
            if ( $attribute->DataTypeString == 'xrowmetadata' and $attribute->hasContent() )
            {
                return $attribute->content();
            }
        }
        return false;
    }
}

?>