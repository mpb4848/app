<?php
/**
 * @author ADi
 */
class StructuredData {
	/**
	 * @var StructuredDataAPIClient
	 */
	protected $APIClient = null;
	/**
	 * @var SDContext
	 */
	private $context = null;

	public function __construct( StructuredDataAPIClient $apiClient ) {
		$this->context = F::build( 'SDContext', array( 'apiClient' => $apiClient ) );
		$this->APIClient = $apiClient;
	}

	public function getSDElement($id) {
		$element = $this->APIClient->getObject($id);
		$template = $this->APIClient->getTemplate( $element->type );

		$SDElement = F::build( 'SDElement', array( 'template' => $template, 'context' => $this->context, 'data' => $element ), 'newFromTemplate');

		return $SDElement;
	}

}