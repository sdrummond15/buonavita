<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * @package jDownloads
 * @version 3.9  
 * Some parts from the search component (and search content plugin) adapted and modified to can use it in jDownloads as an internal search function. 
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Search helper.
 *
 */
class JDSearchHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 */
	public static function addSubmenu($vName)
	{
		// Not required.
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$assetName = 'com_jdownloads';

		$actions = JAccess::getActions($assetName);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	static function santiseSearchWord(&$searchword, $searchphrase)
	{
		$ignored = false;

		$lang = JFactory::getLanguage();

		$tag			= $lang->getTag();
		$search_ignore	= $lang->getIgnoredSearchWords();

		// Deprecated in 1.6 use $lang->getIgnoredSearchWords instead
		$ignoreFile		= $lang->getLanguagePath() . '/' . $tag . '/' . $tag.'.ignore.php';
		if (file_exists($ignoreFile)) {
			include $ignoreFile;
		}

		// check for words to ignore
		$aterms = explode(' ', StringHelper::strtolower($searchword));

		// first case is single ignored word
		if (count($aterms) == 1 && in_array(StringHelper::strtolower($searchword), $search_ignore)){
			$ignored = true;
		}

		// filter out search terms that are too small
		$lower_limit = $lang->getLowerLimitSearchWord();
		foreach($aterms as $aterm) {
			if (StringHelper::strlen($aterm) < $lower_limit){
				$search_ignore[] = $aterm;
			}
		}

		// next is to remove ignored words from type 'all' or 'any' (not exact) searches with multiple words
		if (count($aterms) > 1 && $searchphrase != 'exact') {
			$pruned = array_diff($aterms, $search_ignore);
			$searchword = implode(' ', $pruned);
		}

		return $ignored;
	}

	static function limitSearchWord(&$searchword)
	{
		$restriction = false;

		$lang = JFactory::getLanguage();

		// limit searchword to a maximum of characters
		$upper_limit = $lang->getUpperLimitSearchWord();
		if (StringHelper::strlen($searchword) > $upper_limit) {
			$searchword		= StringHelper::substr($searchword, 0, $upper_limit - 1);
			$restriction	= true;
		}

		// searchword must contain a minimum of characters
		if ($searchword && StringHelper::strlen($searchword) < $lang->getLowerLimitSearchWord()) {
			$searchword		= '';
			$restriction	= true;
		}

		return $restriction;
	}

	/**
	 * Prepares results from search for display
	 *
	 * @param string The source string
	 * @param string The searchword to select around
	 * @return string
	 */
	public static function prepareSearchContent($text, $searchword)
	{
		// strips tags won't remove the actual jscript
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
		$text = preg_replace('/{.+?}/', '', $text);
		//$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text);
		// replace line breaking tags with whitespace
		$text = preg_replace("'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text);

		return self::_smartSubstr(strip_tags($text), $searchword);
	}

	/**
	 * Checks an object for search terms (after stripping fields of HTML)
	 *
	 * @param object The object to check
	 * @param string Search words to check for
	 * @param array List of object variables to check against
	 * @returns boolean True if searchTerm is in object, false otherwise
	 */
	public static function checkNoHtml($object, $searchTerm, $fields)
	{
		$searchRegex = array(
				'#<script[^>]*>.*?</script>#si',
				'#<style[^>]*>.*?</style>#si',
				'#<!.*?(--|]])>#si',
				'#<[^>]*>#i'
				);
		$terms = explode(' ', $searchTerm);

        if (empty($fields)){
            return false;
        }

		foreach($fields as $field) {
            if (!isset($object->$field)){
                continue;
            }

            $text = self::remove_accents($object->$field);

			foreach($searchRegex as $regex) {
				$text = preg_replace($regex, '', $text);
			}
			foreach($terms as $term) {
                $term = self::remove_accents($term);

                if (StringHelper::stristr($text, $term) !== false){
					return true;
				}
			}
		}
		return false;
	}

	/**
     * Transliterates given text to ASCII.
	 *
     * @param   string  $str  String to remove accents from.
     *
	 * @return string
     *
     * @since   3.2
	 */
    public static function remove_accents($str)
	{
        $str = JLanguageTransliterate::utf8_latin_to_ascii($str);

        // @TODO: remove other prefixes as well?
        return preg_replace("/[\"'^]([a-z])/ui", '\1', $str);
    }    
    
    /**
     * Returns substring of characters around a searchword.
     *
     * @param   string   $text        The source string.
     * @param   integer  $searchword  Number of chars to return.
     *
     * @return  string
     *
     * @since   1.5
     */
    public static function _smartSubstr($text, $searchword)
    {
		$lang = JFactory::getLanguage();
		$length = $lang->getSearchDisplayedCharactersNumber();
        $ltext       = self::remove_accents($text);
        $textlen     = StringHelper::strlen($ltext);
        $lsearchword = StringHelper::strtolower(self::remove_accents($searchword));
		$wordfound = false;
		$pos = 0;

        while ($wordfound === false && $pos < $textlen)
        {
            if (($wordpos = @StringHelper::strpos($ltext, ' ', $pos + $length)) !== false)
            {
				$chunk_size = $wordpos - $pos;
            }
            else
            {
				$chunk_size = $length;
			}

            $chunk     = StringHelper::substr($ltext, $pos, $chunk_size);
            $wordfound = StringHelper::strpos(StringHelper::strtolower($chunk), $lsearchword);

            if ($wordfound === false)
            {
				$pos += $chunk_size + 1;
			}
		}

        if ($wordfound !== false)
        {
            return (($pos > 0) ? '...&#160;' : '') . StringHelper::substr($text, $pos, $chunk_size) . '&#160;...';
        }
        else
        {
            if (($wordpos = @StringHelper::strpos($text, ' ', $length)) !== false)
            {
                return StringHelper::substr($text, 0, $wordpos) . '&#160;...';
            }
            else
            {
                return StringHelper::substr($text, 0, $length);
			}
		}
	}
}
