<?php
class RssConnection {
	
	protected $url;
	
	protected $cResource;
	
	protected $xmlData;
	
	function __construct($url) {
		$this->url = $url;
		
		$this->cResource = curl_init($this->url);
		curl_setopt($this->cResource, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->cResource, CURLOPT_CONNECTTIMEOUT, 3);
		
		$c = curl_exec($this->cResource);
		
		if(substr($c, 0, 1) == '<')
			$this->xmlData = new SimpleXMLElement($c);
	}
	
	public function getXmlData() {
		return $this->xmlData;
	}
}


class IpBoardConnection extends RssConnection {
	
	protected $url = "http://www.balmaethor.com/forums/ssi.php?";
	
	protected $data;
	
	public function __construct($mode, $limit, $id=null) {
		
		switch ($mode) {
			case 'active':
				$path = "a=active";
			break;
			case 'news':
				$path = "a=news";
			break;
			case 'out':
				if(isset($id))
					$path = "a=out&f=$id";
				else 
					$path = "a=out&f=2";
			break;
			case 'stats':
				$path = "a=stats";
			break;
			default:
				$path = "a=news";
			break;
		}
		
		$path .= "&show=";
		
		$path .= (!$limit)?$limit:"5";
		
		$cUrl = $this->url . $path;
		
		parent::__construct ($cUrl);
		
		$xml = self::getXmlData();
		
		$dataArray = $xml->channel->item;

		for($i = 0; $i < count($dataArray); $i++) {
			$this->data[] = $dataArray[$i];
		}
		
	}
	
	public function getData() {
		return $this->data;
	}
}

class IpBoardOutItem {
	
	protected $title;
	
	protected $link;
	
	protected $pubDate;
	
	
	function __construct(SimpleXMLElement $data) {

		$this->title = sprintf('%s', $data->title);
		
		$this->link = sprintf('%s', $data->link);
		
		$this->pubDate = preg_replace('/\+\d{4}/', '', $data->pubDate);
		
	}
	
	/**
	 * @return $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return $link
	 */
	public function getLink() {
		return $this->link;
	}
	
	public function getId()
	{
		$id = explode("showtopic=", $this->link);
		return $id[1];
	}

	/**
	 * @return $pubDate
	 */
	public function getPubDate() {
		return $this->pubDate;
	}
	
	public static function fetchItems($limit, $id) {
		$feed = new IpBoardConnection('out', $limit, $id);
		
		$data = $feed->getData();
		
		if(is_array($data)) {
			foreach($data as $key => $item) {
				$items[] = new IpBoardOutItem($item);
			}
		}
		
		return $items;
	}
}


function bmusers_help($path, $args)
{
	switch ($path)
	{
		case "admin/?q=help#bmusers":
			return "<p>" . t("Handles authentication using IPB based user data") . "</p>";
			break;
	}
}

function bmusers_block_info()
{
	$blocks['bmusers'] = array(
		'info'	=> t('User Panel'),
		'cache'	=> DRUPAL_CACHE_PER_ROLE
	);
	
	$blocks['bmtopics'] = array(
		'info'	=> t('Forum Topics'),
	);
	
	$blocks['bmrecent'] = array(
		'info'	=> t('Recent News')
	);
	
	return $blocks;
}

function bmusers_check_login_status()
{
	if(isset($_COOKIE['member_id']))
	{
		$memberId = $_COOKIE['member_id'];
	}
	else 
	{
		$memberId = 0;
	}
	
	if($memberId > 0)
	{
		$session = $_COOKIE['session_id'];
		$pass = $_COOKIE['pass_hash'];
		
		$sql = sprintf("SELECT m.member_id, m.name, m.member_login_key as pass, m.members_pass_salt as salt, s.id AS session_id FROM bm_members m JOIN bm_sessions s ON s.member_id=m.member_id WHERE m.member_id ='%s' LIMIT 1", mysql_real_escape_string($memberId));
		
		$result = db_query($sql);
		
		while($record = $result->fetchObject())
		{
			if($record->member_id == $memberId && $session == $record->session_id && $pass == $record->pass)
			{
				return 1;
			}
			else 
			{
				return 0;
			}
		}
	}
	else 
	{
		return 0;
	}
}

function bmusers_message_count($id)
{
	$sql = sprintf("SELECT COUNT(map_has_unread) as count FROM bm_message_topic_user_map WHERE map_user_id='%d' AND map_has_unread='1'", mysql_real_escape_string($id));
	
	$result = db_query($sql);
	
	return $result;
}

function bmusers_fetch_forum_topics()
{
	$topics = IpBoardOutItem::fetchItems(5, 2);
	
	return $topics;
}

function bmusers_fetch_recent_headlines($limit=1)
{
	$sql = "SELECT b.body_summary, n.title, n.nid, n.created " .
			    				"FROM {field_data_body} b " .
			    				"JOIN {node} n ON n.nid = b.entity_id " .
			    				"JOIN {field_data_field_tags} t ON n.nid = t.entity_id " .
			    				"WHERE n.type = 'article' AND t.field_tags_tid = '1' " .
			    				"ORDER BY n.created DESC LIMIT 5";
	$query = db_query($sql);
		
	return $query;
}


function bmusers_block_view($delta = '')
{
	switch($delta)
	{
		case 'bmusers':
			$block['subject'] = t('User Panel');
			
			$auth = bmusers_check_login_status();
			
			if($auth)
			{
				$block['content'] = t("Logged in!", "/");
			}
			else 
			{
				$block['content'] = l("Log In", "/");
			}
			
			break;
		case 'bmtopics':
			
			//Header
			$block['subject'] = t("Recent Forum Topics");
			
			//Base url for the link to the topic
			$baseurl =  "http://www.balmaethor.com/forums/index.php?showtopic=";
			
			//Array of topics retrieved from the forums
			$topics = bmusers_fetch_forum_topics();
			
			//Ensure we have topics
			if(is_array($topics) && count($topics) > 0)
			{
				$items = array();
				
				//Iterate through the topics to create a list
				foreach($topics as $key => $topic)
				{
					//Exploding the string to grab the id
					$id = $topic->getId();
					
					//Building the list array
					$items[] = array(
						'data' 	=> l($topic->getTitle(), $baseurl . $id) . " <span class='topic_date'>" . t($topic->getPubDate()) . "</span>"
					);					
				}
				
				//Setting the items in the theme's list
				$block['content'] = theme("item_list", array('items' => $items));
			}
			
			break;
			case 'bmrecent':
				
				$block['subject'] = t("Latest News");
				
				if(user_access('access content'))
				{
					$headlines = bmusers_fetch_recent_headlines(5);
					
					if(!empty($headlines))
					{
						$items = array();
						
						while($row = $headlines->fetchObject())
						{
							$date = date('d M \'y', $row->created);
							
							$items[] = array(
								'data'	=> l($row->title, drupal_get_path_alias("node/" . $row->nid)) . " <span class='topic_date'>" . t($date) . "</span>"
							);
						}
						
						$block['content'] = theme("item_list", array('items' => $items));
					}
					else 
					{
						$block['content'] = t("No posts as of yet!");
					}
				}
				else
				{
					$block['content'] = t("Permissions Error");
				}
				
				break;
	}
	return $block;
}


