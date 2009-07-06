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
                if ( isset( $namedParameters['node_id'] ) )
                {
                    $node = eZContentObjectTreeNode::fetch( $namedParameters['node_id'] );
                    if( $node instanceof eZContentObjectTreeNode )
                    {
                        $operatorValue = xrowMetaDataFunctions::fetchByObject( $node->attribute( 'object' ) );
                    }
                    else
                    {
                        $operatorValue = false;
                    }
                }
                else
                {
                    $operatorValue = false;
                }
            }break;
        }
    }
}

?>