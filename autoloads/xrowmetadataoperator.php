<?php

class xrowMetaDataOperator
{

    function operatorList()
    {
        return array( 
            'metadata' 
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 
            'metadata' => array( 
                'node_id' => array( 
                    'type' => 'int' , 
                    'required' => true , 
                    'default' => null 
                ) 
            ) 
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'metadata':
                {
                    var_dump($namedParameters);
                    if ( isset( $namedParameters['node_id'] ) )
                    {
                        $node = eZContentObjectTreeNode::fetch( $namedParameters['node_id'] );
                        $operatorValue = xrowMetaDataFunctions::fetchByObject( $node->attribute( 'object' ) );
                    }
                }
                break;
        }
    }
}

?>