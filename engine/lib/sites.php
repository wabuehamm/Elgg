<?php

	/**
	 * Elgg sites
	 * Functions to manage multiple or single sites in an Elgg install
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Marcus Povey <marcus@dushka.co.uk>
	 * @copyright Curverider Ltd 2008
	 * @link http://elgg.org/
	 */

	/**
	 * ElggSite
	 * Representation of a "site" in the system.
	 * @author Marcus Povey <marcus@dushka.co.uk>
	 * @package Elgg
	 * @subpackage Core
	 */
	class ElggSite extends ElggEntity
	{
		/**
		 * Initialise the attributes array. 
		 * This is vital to distinguish between metadata and base parameters.
		 * 
		 * Place your base parameters here.
		 */
		protected function initialise_attributes()
		{
			parent::initialise_attributes();
			
			$this->attributes['type'] = "site";
			$this->attributes['name'] = "";
			$this->attributes['description'] = "";
			$this->attributes['url'] = "";
		}
				
		/**
		 * Construct a new site object, optionally from a given id value.
		 *
		 * @param mixed $guid If an int, load that GUID. 
		 * 	If a db row then will attempt to load the rest of the data.
		 * @throws Exception if there was a problem creating the site. 
		 */
		function __construct($guid = null) 
		{			
			$this->initialise_attributes();
			
			if (!empty($guid))
			{
				// Is $guid is a DB row - either a entity row, or a site table row.
				if ($guid instanceof stdClass) {			
					// Load the rest
					if (!$this->load($guid->guid))
						throw new IOException("Failed to load new ElggSite from GUID:$guid->guid"); 
				}
				
				// Is $guid is an ElggSite? Use a copy constructor
				else if ($guid instanceof ElggSite)
				{				
					 foreach ($guid->attributes as $key => $value)
					 	$this->attributes[$key] = $value;
				}
				
				// Is this is an ElggEntity but not an ElggSite = ERROR!
				else if ($guid instanceof ElggEntity)
					throw new InvalidParameterException("Passing a non-ElggSite to an ElggSite constructor!");
					
				// See if this is a URL
				else if (strpos($guid, "http")!==false)
				{					
					$guid = get_site_by_url($guid);
					foreach ($guid->attributes as $key => $value)
					 	$this->attributes[$key] = $value;
					 	
				}
					
				// We assume if we have got this far, $guid is an int
				else if (is_numeric($guid)) {				
					if (!$this->load($guid)) throw new IOException("Could not create a new ElggSite object from GUID:$guid");
				}
				
				else
					throw new IOException("Unrecognised value passed to constuctor.");
			}
		}
		
		/**
		 * Class member get overloading
		 *
		 * @param string $name
		 * @return mixed
		 */
		function __get($name) { return $this->get($name); }
		
		/**
		 * Class member set overloading
		 *
		 * @param string $name
		 * @param mixed $value
		 * @return mixed
		 */
		function __set($name, $value) { return $this->set($name, $value); }
		
		/**
		 * Override the load function.
		 * This function will ensure that all data is loaded (were possible), so
		 * if only part of the ElggSite is loaded, it'll load the rest.
		 * 
		 * @param int $guid 
		 */
		protected function load($guid)
		{			
			// Test to see if we have the generic stuff
			if (!parent::load($guid)) 
				return false;

			// Check the type
			if ($this->attributes['type']!='site')
				throw new InvalidClassException("GUID:$guid is not a valid ElggSite");
				
			// Load missing data
			$row = get_site_entity_as_row($guid);
						
			// Now put these into the attributes array as core values
			$objarray = (array) $row;
			foreach($objarray as $key => $value) 
				$this->attributes[$key] = $value;
			
			return true;
		}
		
		/**
		 * Override the save function.
		 */
		public function save()
		{
			// Save generic stuff
			if (!parent::save())
				return false;
			
			// Now save specific stuff
			return create_site_entity($this->get('guid'), $this->get('name'), $this->get('description'), $this->get('url'));
		}
		
		/**
		 * Delete this site.
		 */
		public function delete() 
		{ 
			if (!parent::delete())
				return false;
				
			return delete_site_entity($this->get('guid'));
		}
		
		/**
		 * Return a list of users using this site.
		 *
		 * @param int $limit
		 * @param int $offset
		 * @return array of ElggUsers
		 */
		public function getMembers($limit = 10, $offset = 0) { get_site_members($this->getGUID(), $limit, $offset); }
		
		/**
		 * Add a user to the site.
		 *
		 * @param int $user_guid
		 */
		public function addUser($user_guid) { return add_site_user($this->getGUID(), $user_guid); }
		
		/**
		 * Remove a site user.
		 *
		 * @param int $user_guid
		 */
		public function removeUser($user_guid) { return remove_site_user($this->getGUID(), $user_guid); }
		
		/**
		 * Get an array of member ElggObjects.
		 *
		 * @param string $subtype
		 * @param int $limit
		 * @param int $offset
		 */
		public function getObjects($subtype="", $limit = 10, $offset = 0) { get_site_objects($this->getGUID(), $subtype, $limit, $offset); }
		
		/**
		 * Add an object to the site.
		 *
		 * @param int $user_id
		 */
		public function addObject($object_guid) { return add_site_object($this->getGUID(), $object_guid); }
		
		/**
		 * Remove a site user.
		 *
		 * @param int $user_id
		 */
		public function removeObject($object_guid) { return remove_site_object($this->getGUID(), $object_guid); }

		/**
		 * Get the collections associated with a site.
		 *
		 * @param string $type
		 * @param int $limit
		 * @param int $offset
		 * @return unknown
		 */
		public function getCollections($subtype="", $limit = 10, $offset = 0) { get_site_collections($this->getGUID(), $subtype, $limit, $offset); }
		

		
	}

	/**
	 * Return the site specific details of a site by a row.
	 * 
	 * @param int $guid
	 */
	function get_site_entity_as_row($guid)
	{
		global $CONFIG;
		
		$guid = (int)$guid;
		
		return get_data_row("SELECT * from {$CONFIG->dbprefix}sites_entity where guid=$guid");
	}
	
	/**
	 * Create or update the extras table for a given site.
	 * Call create_entity first.
	 * 
	 * @param int $guid
	 * @param string $name
	 * @param string $description
	 * @param string $url
	 */
	function create_site_entity($guid, $name, $description, $url)
	{
		global $CONFIG;
		
		$guid = (int)$guid;
		$name = sanitise_string($name);
		$description = sanitise_string($description);
		$url = sanitise_string($url);
		
		$row = get_entity_as_row($guid);
		
		if ($row)
		{
			// Exists and you have access to it
			
			// Delete any existing stuff
			delete_site_entity($guid);

			// Insert it
			$result = insert_data("INSERT into {$CONFIG->dbprefix}sites_entity (guid, name, description, url) values ($guid, '$name','$description','$url')");
			if ($result!==false) {
				get_entity($guid);
				if (trigger_event('create',$entity->type,$entity)) {
					return true;
				} else {
					delete_entity($guid);
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Delete a site's extra data.
	 * 
	 * @param int $guid
	 */
	function delete_site_entity($guid)
	{
		global $CONFIG;
		
		$guid = (int)$guid;
		
		$row = get_entity_as_row($guid);
		
		// Check to see if we have access and it exists
		if ($row) 
		{
			// Delete any existing stuff
			return delete_data("DELETE from {$CONFIG->dbprefix}sites_entity where guid=$guid");
		}
		
		return false;
	}
		
	/**
	 * Add a user to a site.
	 * 
	 * @param int $site_guid 
	 * @param int $user_guid
	 */
	function add_site_user($site_guid, $user_guid)
	{
		global $CONFIG;
		
		$site_guid = (int)$site_guid;
		$user_guid = (int)$user_guid;
		
		return add_entity_relationship($user_guid, "member_of_site", $site_guid);
	}
	
	/**
	 * Remove a user from a site.
	 * 
	 * @param int $site_guid 
	 * @param int $user_guid
	 */
	function remove_site_user($site_guid, $user_guid)
	{
		$site_guid = (int)$site_guid;
		$user_guid = (int)$user_guid;
		
		return remove_entity_relationship($user_guid, "member_of_site", $site_guid);
	}
	
	/**
	 * Get the members of a site.
	 * 
	 * @param int $site_guid
	 * @param int $limit 
	 * @param int $offset
	 */
	function get_site_members($site_guid, $limit = 10, $offset = 0)
	{
		$site_guid = (int)$site_guid;
		$limit = (int)$limit;
		$offset = (int)$offset;
		
		return get_entities_from_relationship("member_of_site", $site_guid, true, "user", "", 0, "time_created desc", $limit, $offset);
	}
	
	/**
	 * Add an object to a site.
	 * 
	 * @param int $site_guid 
	 * @param int $object_guid
	 */
	function add_site_object($site_guid, $object_guid)
	{
		global $CONFIG;
		
		$site_guid = (int)$site_guid;
		$object_guid = (int)$object_guid;
		
		return add_entity_relationship($object_guid, "member_of_site", $site_guid);
	}
	
	/**
	 * Remove an object from a site.
	 * 
	 * @param int $site_guid 
	 * @param int $object_guid
	 */
	function remove_site_object($site_guid, $object_guid)
	{
		$site_guid = (int)$site_guid;
		$object_guid = (int)$object_guid;
		
		return remove_entity_relationship($object_guid, "member_of_site", $site_guid);
	}
	
	/**
	 * Get the objects belonging to a site.
	 * 
	 * @param int $site_guid
	 * @param string $subtype
	 * @param int $limit 
	 * @param int $offset
	 */
	function get_site_objects($site_guid, $subtype = "", $limit = 10, $offset = 0)
	{
		$site_guid = (int)$site_guid;
		$subtype = sanitise_string($subtype);
		$limit = (int)$limit;
		$offset = (int)$offset;
		
		return get_entities_from_relationship("member_of_site", $site_guid, true, "object", $subtype, 0, "time_created desc", $limit, $offset);
	}
	
	/**
	 * Add a collection to a site.
	 * 
	 * @param int $site_guid 
	 * @param int $collection_guid
	 */
	function add_site_collection($site_guid, $collection_guid)
	{
		global $CONFIG;
		
		$site_guid = (int)$site_guid;
		$collection_guid = (int)$collection_guid;
		
		return add_entity_relationship($collection_guid, "member_of_site", $site_guid);
	}
	
	/**
	 * Remove a collection from a site.
	 * 
	 * @param int $site_guid 
	 * @param int $collection_guid
	 */
	function remove_site_collection($site_guid, $collection_guid)
	{
		$site_guid = (int)$site_guid;
		$collection_guid = (int)$collection_guid;
		
		return remove_entity_relationship($collection_guid, "member_of_site", $site_guid);
	}
	
	/**
	 * Get the collections belonging to a site.
	 * 
	 * @param int $site_guid
	 * @param string $subtype
	 * @param int $limit 
	 * @param int $offset
	 */
	function get_site_collections($site_guid, $subtype = "", $limit = 10, $offset = 0)
	{
		$site_guid = (int)$site_guid;
		$subtype = sanitise_string($subtype);
		$limit = (int)$limit;
		$offset = (int)$offset;
		
		return get_entities_from_relationship("member_of_site", $site_guid, true, "collection", $subtype, 0, "time_created desc", $limit, $offset);
	}
	
	/**
	 * Return the site via a url.
	 */
	function get_site_by_url($url)
	{
		global $CONFIG;
		
		$url = sanitise_string($url);
		
		$row = get_data_row("SELECT * from {$CONFIG->dbprefix}sites_entity where url='$url'");
	
		if ($row)
			return new ElggSite($row); 
		
		return false;
	}
	
	
	
	
	/**
	 * Initialise site handling
	 *
	 * Called at the beginning of system running, to set the ID of the current site.
	 * This is 0 by default, but plugins may alter this behaviour by attaching functions
	 * to the sites init event and changing $CONFIG->site_id.
	 * 
	 * @uses $CONFIG
	 * @param string $event Event API required parameter
	 * @param string $object_type Event API required parameter
	 * @param null $object Event API required parameter
	 * @return true
	 */
		function sites_init($event, $object_type, $object) {
			
			global $CONFIG;
			$site = trigger_plugin_hook("siteid","system");
			if ($site === null || $site === false) {
				$CONFIG->site_id = (int) datalist_get('default_site');
			} else {
				$CONFIG->site_id = $site;
			}
			$CONFIG->site_guid = $CONFIG->site_id;
			$CONFIG->site = get_entity($CONFIG->site_guid);
			
			return true;
			
		}
		
	// Register event handlers

		register_event_handler('boot','system','sites_init',2);

?>