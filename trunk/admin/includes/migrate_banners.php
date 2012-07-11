<?php
/**
 * jUpgrade
 *
 * @version             $Id$
 * @package             MatWare
 * @subpackage          com_jupgrade
 * @copyright           Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license             GNU General Public License version 2 or later.
 * @author              Matias Aguirre <maguirre@matware.com.ar>
 * @link                http://www.matware.com.ar
 */

/**
 * Upgrade class for Banners
 *
 * This class takes the banners from the existing site and inserts them into the new site.
 *
 * @since       0.4.5
 */
class jUpgradeBanners extends jUpgrade
{
	/**
	 * @var         string  The name of the source database table.
	 * @since       0.4.5
	 */
	protected $source = '#__banner';

	/**
	 * @var         string  The name of the destination database table.
	 * @since       0.4.5
	 */
	protected $destination = '#__banners';


	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return      array   Returns a reference to the source data array.
	 * @since       0.4.5
	 * @throws      Exception
	 */
	protected function &getSourceData()
	{
	        $rows = parent::getSourceData(
	                '`bid` AS id, `cid`, `type`, `name`, `alias`, `imptotal`, `impmade`, '
	                .'`clicks`, `imageurl`, `clickurl`, `date`, `showBanner` AS state, `checked_out`, '
	                .'`checked_out_time`, `editor`, `custombannercode`, `catid`, `description`, '
	                .'`sticky`, `ordering`, `publish_up`, `publish_down`, `tags`, `params`', null, 'bid'); 

	        // Getting the categories id's
	        $categories = $this->getMapList('categories', 'com_banners');

	        // Do some custom post processing on the list.
	        foreach ($rows as $index => &$row)
	        {    
	        		// Convert HTML entities to UTF-8 on escaped entries
	        		$row['name'] = $this->entities2Utf8($row['name']);
	        	
	                $row['params'] = $this->convertParams($row['params']);                        

	                $cid = $row['catid'];
	                $row['catid'] = &$categories[$cid]->new;
	        }

	        return $rows;
	}

	/**
	 * Sets the data in the destination database.
	 *
	 * @return      void
	 * @since       0.4.
	 * @throws      Exception
	 */
	protected function setDestinationData()
	{
		$rows = $this->getSourceData();

		foreach($rows as &$row)
	        {
			$temp = new JParameter($row['params']);
			$temp->set('imageurl', 'images/banners/' . $row['imageurl']);
			$row['params'] = json_encode($temp->toObject());

			$row['language'] = '*';

			unset($row['imageurl']);
			unset($row['date']);
			unset($row['editor']);
			unset($row['tags']);
		}

		parent::setDestinationData($rows);
	}
}

/**
 * Upgrade class for banners clients 
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @since		2.5.2
 */
class jUpgradeBannersClients extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	2.5.2
	 */
	protected $source = '#__bannerclient';

	/**
	 * @var         string  The name of the destination database table.
	 * @since       2.5.2
	 */
	protected $destination = '#__banner_clients';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return      array   Returns a reference to the source data array.
	 * @since       2.5.2
	 * @throws      Exception
	 */
	protected function &getSourceData()
	{
		$rows = parent::getSourceData('`cid` AS id, `name`, `contact`, `email`, `extrainfo`, `checked_out`, `checked_out_time`'); 

		return $rows;
	}
}

/**
 * Upgrade class for banners tracks 
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @since		2.5.2
 */
class jUpgradeBannersTracks extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	2.5.2
	 */
	protected $source = '#__bannertrack';

	/**
	 * @var         string  The name of the destination database table.
	 * @since       2.5.2
	 */
	protected $destination = '#__banner_tracks';
}
