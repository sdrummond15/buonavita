<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Form Field to load a list of states
 *
 */
class JFormFieldjdStatus extends JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'jdStatus';

	/**
	 * Available statuses
	 *
	 * @var  array
	 */
	protected $predefinedOptions = array(
		'0'  => 'JUNPUBLISHED',
		'1'  => 'JPUBLISHED',
		'*'  => 'JALL',
	);
}
