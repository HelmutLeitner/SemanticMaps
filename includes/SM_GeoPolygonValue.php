<?php

class SMGeoPolygonValue extends SMWDataValue{

	protected $wikiValue;

	/**
	 * Initialise the datavalue from the given value string.
	 * The format of this strings might be any acceptable user input
	 * and especially includes the output of getWikiValue().
	 *
	 * @param string $value
	 */
	protected function parseUserValue( $value ) {
		$this->wikiValue = $value;

		if ( $value === '' ) {
			$this->addError( wfMsg( 'smw_novalues' ) );
		} else {
			$comparator = SMW_CMP_EQ;
			SMWDataValue::prepareValue( $value , $comparator );

			$coordinates = explode(SMWDIGeoPolygon::POLYGON_DELIMITER,$value);

			if ( is_null( $this->m_contextPage ) ) {
				$semanticData = SMWContainerSemanticData::makeAnonymousContainer();
			} else {
				$subobjectName = '_' . hash( 'md4', $value, false ); // md4 is probably fastest of PHP's hashes
				$subject = new SMWDIWikiPage( $this->m_contextPage->getDBkey(),
					$this->m_contextPage->getNamespace(), $this->m_contextPage->getInterwiki(),
					$subobjectName );
				$semanticData = new SMWContainerSemanticData( $subject );
			}

			$this->m_dataitem = new SMWDIGeoPolygon( $semanticData );

			foreach($coordinates as $coordinate){
				$coordinate = MapsCoordinateParser::parseCoordinates( $coordinate );
				if ( $coordinate ) {
					$this->m_dataitem->addPolygonPoint(new SMWDIGeoCoord($coordinate));
				} else {
					$this->addError( wfMsgExt( 'maps_unrecognized_coords', array( 'parsemag' ), $coordinates, 1 ) );
				}
			}
		}
	}

	/**
	 * Set the actual data contained in this object. The method returns
	 * true if this was successful (requiring the type of the dataitem
	 * to match the data value). If false is returned, the data value is
	 * left unchanged (the data item was rejected).
	 *
	 * @note Even if this function returns true, the data value object
	 * might become invalid if the content of the data item caused errors
	 * in spite of it being of the right basic type. False is only returned
	 * if the data item is fundamentally incompatible with the data value.
	 *
	 * @since 1.6
	 *
	 * @param $dataitem SMWDataItem
	 *
	 * @return boolean
	 */
	protected function loadDataItem( SMWDataItem $dataItem ) {
		if ( $dataItem->getDIType() == SMWDataItem::TYPE_GEOPOL ) {
			$this->m_dataitem = $dataItem;
			$this->wikiValue = $dataItem->getSerialization();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns a short textual representation for this data value. If the value
	 * was initialised from a user supplied string, then this original string
	 * should be reflected in this short version (i.e. no normalisation should
	 * normally happen). There might, however, be additional parts such as code
	 * for generating tooltips. The output is in wiki text.
	 *
	 * The parameter $linked controls linking of values such as titles and should
	 * be non-NULL and non-false if this is desired.
	 */
	public function getShortWikiText( $linked = null ) {
		if ( $this->isValid() ) {
			if ( $this->m_caption === false ) {
				return $this->m_dataitem->getSerialization();
			}
			else {
				return $this->m_caption;
			}
		}
		else {
			return $this->getErrorText();
		}
	}

	/**
	 * Returns a short textual representation for this data value. If the value
	 * was initialised from a user supplied string, then this original string
	 * should be reflected in this short version (i.e. no normalisation should
	 * normally happen). There might, however, be additional parts such as code
	 * for generating tooltips. The output is in HTML text.
	 *
	 * The parameter $linker controls linking of values such as titles and should
	 * be some Linker object (or NULL for no linking).
	 */
	public function getShortHTMLText( $linker = null ) {
		return $this->getShortWikiText( $linker );
	}

	/**
	 * Return the long textual description of the value, as printed for
	 * example in the factbox. If errors occurred, return the error message
	 * The result always is a wiki-source string.
	 *
	 * The parameter $linked controls linking of values such as titles and should
	 * be non-NULL and non-false if this is desired.
	 */
	public function getLongWikiText( $linked = null ) {
		if ( $this->isValid() ) {
			return 'TODO: return something more meaningfull here';
		} else {
			return $this->getErrorText();
		}
	}

	/**
	 * Return the long textual description of the value, as printed for
	 * example in the factbox. If errors occurred, return the error message
	 * The result always is an HTML string.
	 *
	 * The parameter $linker controls linking of values such as titles and should
	 * be some Linker object (or NULL for no linking).
	 */
	public function getLongHTMLText( $linker = null ) {
		return $this->getLongWikiText( $linker );
	}

	/**
	 * Return the plain wiki version of the value, or
	 * FALSE if no such version is available. The returned
	 * string suffices to reobtain the same DataValue
	 * when passing it as an input string to setUserValue().
	 */
	public function getWikiValue() {
		return $this->wikiValue;
	}
}
