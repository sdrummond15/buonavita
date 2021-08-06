<?php

defined('_JEXEC') or die();

jimport( 'joomla.application.component.modellist' );


class jdownloadsModelUninstall extends JModelList
{
	/**
	 * jDownloads data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
    }

    /**
     * Method to get the record form.
     *
     * @param    array    $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return    mixed    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = false)
    {

        // Initialise variables.
        $app    = JFactory::getApplication();

        // Get the form.
        $form = $this->loadForm('com_jdownloads.uninstall', 'uninstall', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form))
        {
            return false;
        }
        return $form;
    }

}
