<?php

class Flattr_Xml
{
	
	/**
	 * Checks if node has any children other than just text
	 *
	 * @param DOMNode
	 * @return boolean
	 */
	public static function nodeHasChild( $node )
	{
		if ( $node->hasChildNodes() )
		{
			foreach ( $node->childNodes as $child )
			{
				if ( $child->nodeType == XML_ELEMENT_NODE )
				{
	    			return true;
				}
			}
		}

		return false;
	}

	/**
	 * Takes a DOMNode (or a DOMNodeList) and returns it as an array
	 * 
	 * @param DOMNode|DOMNodeList $item
	 * @return array
	 */
	public static function toArray( $xml )
	{
		if ( $xml instanceOf DOMNodeList )
		{
			$items = array();
			foreach ( $xml as $item )
			{
				$items[] = self::toArray( $item );
			}
	
			return $items;
		}
	
		$itemData = array();
		foreach ( $xml->childNodes as $node )
		{
			if ( self::nodeHasChild( $node ) )
			{
				$itemData[$node->nodeName] = self::toArray( $node );
			}
			else
			{
				$itemData[$node->nodeName] = $node->nodeValue;
			}
		}

		return $itemData;
	}

}