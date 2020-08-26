<?php
error_reporting(0);



#####################
### CONFIG OF BOT ###
#####################
define('DEBUG_FILE_NAME', ''); // if you need read debug log, you should write unique log name
define('CLIENT_ID', ''); // like 'app.67efrrt2990977.85678329' or 'local.57062d3061fc71.97850406' - This code should take in a partner's site, needed only if you want to write a message from Bot at any time without initialization by the user
define('CLIENT_SECRET', ''); // like '8bb00435c88aaa3028a0d44320d60339' - TThis code should take in a partner's site, needed only if you want to write a message from Bot at any time without initialization by the user
#####################




writeToLog($_REQUEST, 'ImBot Event Query');

$appsConfig = Array();
if (file_exists(__DIR__.'/config.php'))
	include(__DIR__.'/config.php');

// receive event "new message for bot"
if ($_REQUEST['event'] == 'ONIMBOTMESSAGEADD')
{
	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
		return false;

	if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] != 'LINES')
		return false;

	itrRun($_REQUEST['auth']['application_token'], $_REQUEST['data']['PARAMS']['DIALOG_ID'], $_REQUEST['data']['PARAMS']['FROM_USER_ID'], $_REQUEST['data']['PARAMS']['MESSAGE']);
}
if ($_REQUEST['event'] == 'ONIMBOTJOINCHAT')
{
	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
		return false;

	if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] != 'LINES')
		return false;

	itrRun($_REQUEST['auth']['application_token'], $_REQUEST['data']['PARAMS']['DIALOG_ID'], $_REQUEST['data']['PARAMS']['USER_ID']);
}
// receive event "delete chat-bot"
else if ($_REQUEST['event'] == 'ONIMBOTDELETE')
{
	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
		return false;

	// unset application variables
	unset($appsConfig[$_REQUEST['auth']['application_token']]);

	// save params
	saveParams($appsConfig);

	// write debug log
	writeToLog($_REQUEST['event'], 'ImBot unregister');
}
// receive event "Application install"
else if ($_REQUEST['event'] == 'ONAPPINSTALL')
{
	// handler for events
	$handlerBackUrl = ($_SERVER['SERVER_PORT']==443||$_SERVER["HTTPS"]=="on"? 'https': 'http')."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']).$_SERVER['SCRIPT_NAME'];

	// If your application supports different localizations
	// use $_REQUEST['data']['LANGUAGE_ID'] to load correct localization

	// register new bot
	$result = restCommand('imbot.register', Array(
		'CODE' => 'itrbot',
		'TYPE' => 'O',
		'EVENT_MESSAGE_ADD' => $handlerBackUrl,
		'EVENT_WELCOME_MESSAGE' => $handlerBackUrl,
		'EVENT_BOT_DELETE' => $handlerBackUrl,
		'OPENLINE' => 'Y',
		'PROPERTIES' => Array(
			'NAME' => 'ITR Bot for Open Channels #'.(count($appsConfig)+1),
			'WORK_POSITION' => "Get ITR menu for you open channel",
			'COLOR' => 'RED',
		)
	), $_REQUEST["auth"]);
	$botId = $result['result'];

	$result = restCommand('event.bind', Array(
		'EVENT' => 'OnAppUpdate',
		'HANDLER' => $handlerBackUrl
	), $_REQUEST["auth"]);

	// save params
	$appsConfig[$_REQUEST['auth']['application_token']] = Array(
		'BOT_ID' => $botId,
		'LANGUAGE_ID' => $_REQUEST['data']['LANGUAGE_ID'],
		'AUTH' => $_REQUEST['auth'],
	);
	saveParams($appsConfig);

	// write debug log
	writeToLog(Array($botId), 'ImBot register');
}
// receive event "Application install"
else if ($_REQUEST['event'] == 'ONAPPUPDATE')
{
	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
		return false;

	if ($_REQUEST['data']['VERSION'] == 2)
	{
		// Some logic in update event for VERSION 2
		// You can execute any method RestAPI, BotAPI or ChatAPI, for example delete or add a new command to the bot
		/*
		$result = restCommand('...', Array(
			'...' => '...',
		), $_REQUEST["auth"]);
		*/

		/*
		For example delete "Echo" command:

		$result = restCommand('imbot.command.unregister', Array(
			'COMMAND_ID' => $appsConfig[$_REQUEST['auth']['application_token']]['COMMAND_ECHO'],
		), $_REQUEST["auth"]);
		*/
	}
	else
	{
		// send answer message
		$result = restCommand('app.info', array(), $_REQUEST["auth"]);
	}

	// write debug log
	writeToLog($result, 'ImBot update event');
}

/**
 * Run ITR menu
 *
 * @param $portalId
 * @param $dialogId
 * @param $userId
 * @param string $message
 * @return bool
 */
function itrRun($portalId, $dialogId, $userId, $message = '')
{
	if ($userId <= 0)
		return false;

	$menu0 = new ItrMenu(0);
	$menu0->setText('Main menu (#0)');
	$menu0->addItem(1, 'Text', ItrItem::sendText('Text message (for #USER_NAME#)'));
	$menu0->addItem(2, 'Text without menu', ItrItem::sendText('Text message without menu', true));
	$menu0->addItem(3, 'Open menu #1', ItrItem::openMenu(1));
	$menu0->addItem(0, 'Wait operator answer', ItrItem::sendText('Wait operator answer', true));

	$menu1 = new ItrMenu(1);
	$menu1->setText('Second menu (#1)');
	$menu1->addItem(2, 'Transfer to queue', ItrItem::transferToQueue('Transfer to queue'));
	$menu1->addItem(3, 'Transfer to user', ItrItem::transferToUser(1, false, 'Transfer to user #1'));
	$menu1->addItem(4, 'Transfer to bot', ItrItem::transferToBot('marta', true, 'Transfer to bot Marta', 'Marta not found :('));
	$menu1->addItem(5, 'Finish session', ItrItem::finishSession('Finish session'));
	$menu1->addItem(6, 'Exec function', ItrItem::execFunction(function($context){
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => 'Function executed (action)',
		), $_REQUEST["auth"]);
		writeToLog($result, 'Exec function');
	}, 'Function executed (text)'));
	$menu1->addItem(9, 'Back to main menu', ItrItem::openMenu(0));

	$itr = new Itr($portalId, $dialogId, 0, $userId);
	$itr->addMenu($menu0);
	$itr->addMenu($menu1);
	$itr->run(prepareText($message));

	return true;
}


/**
 * Save application configuration.
 * WARNING: this method is only created for demonstration, never store config like this
 *
 * @param $params
 * @return bool
 */
function saveParams($params)
{
	$config = "<?php\n";
	$config .= "\$appsConfig = ".var_export($params, true).";\n";
	$config .= "?>";

	file_put_contents(__DIR__."/config.php", $config);

	return true;
}

/**
 * Send rest query to Bitrix24.
 *
 * @param $method - Rest method, ex: methods
 * @param array $params - Method params, ex: Array()
 * @param array $auth - Authorize data, received from event
 * @param boolean $authRefresh - If authorize is expired, refresh token
 * @return mixed
 */
function restCommand($method, array $params = Array(), array $auth = Array(), $authRefresh = true)
{
	$queryUrl = $auth["client_endpoint"].$method;
	$queryData = http_build_query(array_merge($params, array("auth" => $auth["access_token"])));

	writeToLog(Array('URL' => $queryUrl, 'PARAMS' => array_merge($params, array("auth" => $auth["access_token"]))), 'ImBot send data');

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYPEER => 1,
		CURLOPT_URL => $queryUrl,
		CURLOPT_POSTFIELDS => $queryData,
	));

	$result = curl_exec($curl);
	curl_close($curl);

	$result = json_decode($result, 1);

	if ($authRefresh && isset($result['error']) && in_array($result['error'], array('expired_token', 'invalid_token')))
	{
		$auth = restAuth($auth);
		if ($auth)
		{
			$result = restCommand($method, $params, $auth, false);
		}
	}

	return $result;
}

/**
 * Get new authorize data if you authorize is expire.
 *
 * @param array $auth - Authorize data, received from event
 * @return bool|mixed
 */
function restAuth($auth)
{
	if (!CLIENT_ID || !CLIENT_SECRET)
		return false;

	if(!isset($auth['refresh_token']))
		return false;

	$queryUrl = 'https://oauth.bitrix.info/oauth/token/';
	$queryData = http_build_query($queryParams = array(
		'grant_type' => 'refresh_token',
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'refresh_token' => $auth['refresh_token'],
	));

	writeToLog(Array('URL' => $queryUrl, 'PARAMS' => $queryParams), 'ImBot request auth data');

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $queryUrl.'?'.$queryData,
	));

	$result = curl_exec($curl);
	curl_close($curl);

	$result = json_decode($result, 1);
	if (!isset($result['error']))
	{
		$appsConfig = Array();
		if (file_exists(__DIR__.'/config.php'))
			include(__DIR__.'/config.php');

		$result['application_token'] = $auth['application_token'];
		$appsConfig[$auth['application_token']]['AUTH'] = $result;
		saveParams($appsConfig);
	}
	else
	{
		$result = false;
	}

	return $result;
}

/**
 * Write data to log file. (by default disabled)
 * WARNING: this method is only created for demonstration, never store log file in public folder
 *
 * @param mixed $data
 * @param string $title
 * @return bool
 */
function writeToLog($data, $title = '')
{
	if (!DEBUG_FILE_NAME)
		return false;

	$log = "\n------------------------\n";
	$log .= date("Y.m.d G:i:s")."\n";
	$log .= (strlen($title) > 0 ? $title : 'DEBUG')."\n";
	$log .= print_r($data, 1);
	$log .= "\n------------------------\n";

	file_put_contents(__DIR__."/".DEBUG_FILE_NAME, $log, FILE_APPEND);

	return true;
}

/**
 * Clean text before select ITR item
 *
 * @param $message
 * @return string
 */
function prepareText($message)
{
	$message = preg_replace("/\[s\].*?\[\/s\]/i", "-", $message);
	$message = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $message);
	$message = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/i", "$1", $message);
	$message = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixs", "$2", $message);
	$message = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $message);
	$message = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $message);
	$message = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $message);
	$message = preg_replace('#\-{54}.+?\-{54}#s', "", str_replace(array("#BR#"), Array(" "), $message));
	$message = strip_tags($message);

	return trim($message);
}


/**
 * Class Itr
 * @package Bitrix\ImBot\Bot
 */
class Itr
{
	public $botId = 0;
	public $userId = 0;
	public $dialogId = '';
	public $portalId = '';

	private $cacheId = '';
	private static $executed = false;

	private $menuItems = Array();
	private $menuText = Array();

	private $currentMenu = 0;
	private $skipShowMenu = false;

	public function __construct($portalId, $dialogId, $botId, $userId)
	{
		$this->portalId = $portalId;
		$this->userId = $userId;
		$this->botId = $botId;
		$this->dialogId = $dialogId;

		$this->getCurrentMenu();
	}

	public function addMenu(ItrMenu $items)
	{
		$this->menuText[$items->getId()] = $items->getText();
		$this->menuItems[$items->getId()] = $items->getItems();

		return true;
	}

	/**
	 * Get menu state.
	 * WARNING: this method is only created for demonstration, never store cache like this
	 */
	private function getCurrentMenu()
	{
		$this->cacheId = md5($this->portalId.$this->botId.$this->dialogId);

		if (file_exists(__DIR__.'/cache') && file_exists(__DIR__.'/cache/'.$this->cacheId.'.cache'))
		{
			$this->currentMenu = intval(file_get_contents(__DIR__.'/cache/'.$this->cacheId.'.cache'));
		}
		else
		{
			if (!file_exists(__DIR__.'/cache'))
			{
				mkdir(__DIR__.'/cache');
 				chmod(__DIR__.'/cache', 0777);
			}
			file_put_contents(__DIR__.'/cache/'.$this->cacheId.'.cache', 0);
		}
	}

	/**
	 * Save menu state.
	 * WARNING: this method is only created for demonstration, never store cache like this
	 */
	private function setCurrentMenu($id)
	{
		$this->currentMenu = intval($id);
		file_put_contents(__DIR__.'/cache/'.$this->cacheId.'.cache', $this->currentMenu);
	}

	private function execMenuItem($itemId = '')
	{
		if ($itemId === '')
		{
			return true;
		}
		else if ($itemId === "0")
		{
			$this->skipShowMenu = true;
		}

		if (!isset($this->menuItems[$this->currentMenu][$itemId]))
		{
			return false;
		}

		$menuItemAction = $this->menuItems[$this->currentMenu][$itemId]['ACTION'];

		if ($menuItemAction['HIDE_MENU'])
		{
			$this->skipShowMenu = true;
		}

		if (isset($menuItemAction['TEXT']))
		{
			$messageText = str_replace('#USER_NAME#', $_REQUEST["data"]["USER"]["NAME"], $menuItemAction['TEXT']);
			restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $this->dialogId,
				"MESSAGE" => $messageText,
			), $_REQUEST["auth"]);
		}

		if ($menuItemAction['TYPE'] == ItrItem::TYPE_MENU)
		{
			$this->setCurrentMenu($menuItemAction['MENU']);
		}
		else if ($menuItemAction['TYPE'] == ItrItem::TYPE_QUEUE)
		{
			restCommand('imopenlines.bot.session.operator', Array(
				"CHAT_ID" => substr($this->dialogId, 4),
			), $_REQUEST["auth"]);
		}
		else if ($menuItemAction['TYPE'] == ItrItem::TYPE_USER)
		{
			restCommand('imopenlines.bot.session.transfer', Array(
				"CHAT_ID" => substr($this->dialogId, 4),
				"USER_ID" => $menuItemAction['USER_ID'],
				"LEAVE" => $menuItemAction['LEAVE']? 'Y': 'N',
			), $_REQUEST["auth"]);
		}
		else if ($menuItemAction['TYPE'] == ItrItem::TYPE_BOT)
		{
			$botId = 0;
			$result = restCommand('imbot.bot.list', Array(), $_REQUEST["auth"]);
			foreach ($result['result'] as $botData)
			{
				if ($botData['CODE'] == $menuItemAction['BOT_CODE'] && $botData['OPENLINE'] == 'Y')
				{
					$botId = $botData['ID'];
					break;
				}
			}
			if ($botId)
			{
				restCommand('imbot.chat.user.add', Array(
					'CHAT_ID' => substr($this->dialogId, 4),
   					'USERS' => Array($botId)
				), $_REQUEST["auth"]);
				if ($menuItemAction['LEAVE'])
				{
					restCommand('imbot.chat.leave', Array(
						'CHAT_ID' => substr($this->dialogId, 4)
					), $_REQUEST["auth"]);
				}
			}
			else if ($menuItemAction['ERROR_TEXT'])
			{
				$messageText = str_replace('#USER_NAME#', $_REQUEST["data"]["USER"]["NAME"], $menuItemAction['ERROR_TEXT']);
				restCommand('imbot.message.add', Array(
					"DIALOG_ID" => $this->dialogId,
					"MESSAGE" => $messageText,
				), $_REQUEST["auth"]);
				$this->skipShowMenu = false;
			}
		}
		else if ($menuItemAction['TYPE'] == ItrItem::TYPE_FINISH)
		{
			restCommand('imopenlines.bot.session.finish', Array(
				"CHAT_ID" => substr($this->dialogId, 4)
			), $_REQUEST["auth"]);
		}
		else if ($menuItemAction['TYPE'] == ItrItem::TYPE_FUNCTION)
		{
			$menuItemAction['FUNCTION']($this);
		}

		return true;
	}

	private function getMenuItems()
	{
		$messageText = '';
		if ($this->skipShowMenu)
		{
			$this->skipShowMenu = false;
			return $messageText;
		}

		if (isset($this->menuText[$this->currentMenu]))
		{
			$messageText = $this->menuText[$this->currentMenu].'[br]';
		}

		foreach ($this->menuItems[$this->currentMenu] as $itemId => $data)
		{
			$messageText .= '[send='.$itemId.']'.$itemId.'. '.$data['TITLE'].'[/send][br]';
		}

		$messageText = str_replace('#USER_NAME#', $_REQUEST["data"]["USER"]["NAME"], $messageText);
		restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $this->dialogId,
			"MESSAGE" => $messageText,
		), $_REQUEST["auth"]);

		return true;
	}

	public function run($text)
	{
		if (self::$executed)
			return false;

		list($itemId) = explode(" ", $text);

		$this->execMenuItem($itemId);

		$this->getMenuItems();

		self::$executed = true;

		return true;
	}
}

class ItrMenu
{
	private $id = 0;
	private $text = '';
	private $items = Array();

	/**
	 * ItrMenu constructor.
	 * @param $id
	 */
	public function __construct($id)
	{
		$this->id = intval($id);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getText()
	{
		return $this->text;
	}

	public function getItems()
	{
		return $this->items;
	}

	public function setText($text)
	{
		$this->text = trim($text);
	}

	public function addItem($id, $title, array $action)
	{
		$id = intval($id);
		if ($id <= 0 && !in_array($action['TYPE'], Array(ItrItem::TYPE_VOID, ItrItem::TYPE_TEXT)))
		{
			return false;
		}

		$title = trim($title);

		$this->items[$id] = Array(
			'ID' => $id,
			'TITLE' => $title,
			'ACTION' => $action
		);

		return true;
	}
}

class ItrItem
{
	const TYPE_VOID = 'VOID';
	const TYPE_TEXT = 'TEXT';
	const TYPE_MENU = 'MENU';
	const TYPE_USER = 'USER';
	const TYPE_BOT = 'BOT';
	const TYPE_QUEUE = 'QUEUE';
	const TYPE_FINISH = 'FINISH';
	const TYPE_FUNCTION = 'FUNCTION';

	public static function void($hideMenu = true)
	{
		return Array(
			'TYPE' => self::TYPE_VOID,
			'HIDE_MENU' => $hideMenu? true: false
		);
	}

	public static function sendText($text = '', $hideMenu = false)
	{
		return Array(
			'TYPE' => self::TYPE_TEXT,
			'TEXT' => $text,
			'HIDE_MENU' => $hideMenu? true: false
		);
	}

	public static function openMenu($menuId)
	{
		return Array(
			'TYPE' => self::TYPE_MENU,
			'MENU' => $menuId
		);
	}

	public static function transferToQueue($text = '', $hideMenu = true)
	{
		return Array(
			'TYPE' => self::TYPE_QUEUE,
			'TEXT' => $text,
			'HIDE_MENU' => $hideMenu? true: false
		);
	}

	public static function transferToUser($userId, $leave = false, $text = '', $hideMenu = true)
	{
		return Array(
			'TYPE' => self::TYPE_USER,
			'TEXT' => $text,
			'HIDE_MENU' => $hideMenu? true: false,
			'USER_ID' => $userId,
			'LEAVE' => $leave? true: false,
		);
	}

	public static function transferToBot($botCode, $leave = true, $text = '', $errorText = '')
	{
		return Array(
			'TYPE' => self::TYPE_BOT,
			'TEXT' => $text,
			'ERROR_TEXT' => $errorText,
			'HIDE_MENU' => true,
			'BOT_CODE' => $botCode,
			'LEAVE' => $leave? true: false,
		);
	}

	public static function finishSession($text = '')
	{
		return Array(
			'TYPE' => self::TYPE_FINISH,
			'TEXT' => $text,
			'HIDE_MENU' => true
		);
	}

	public static function execFunction($function, $text = '', $hideMenu = false)
	{
		return Array(
			'TYPE' => self::TYPE_FUNCTION,
			'FUNCTION' => $function,
			'TEXT' => $text,
			'HIDE_MENU' => $hideMenu? true: false
		);
	}
}