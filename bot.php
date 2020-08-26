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

	// response time
	$latency = (time()-$_REQUEST['ts']);
	$latency = $latency > 60? (round($latency/60)).'m': $latency."s";

	if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] == 'LINES')
	{
		list($message) = explode(" ", $_REQUEST['data']['PARAMS']['MESSAGE']);
		if ($message == '1')
		{
			$result = restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				"MESSAGE" => 'Im EchoBot, i can repeat message after you and send menu in Open Channels![br]Look new chat-bot created specifically for Open Channels - [b]ITR Bot[/b] http://birix24.ru/~bot-itr',
			), $_REQUEST["auth"]);
		}
		else if ($message == '0')
		{
			$result = restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				"MESSAGE" => 'Wait for an answer!',
			), $_REQUEST["auth"]);
		}
	}
	else
	{
		// send answer message
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => "Message from bot",
			"ATTACH" => Array(
				Array("MESSAGE" => "reply: ".$_REQUEST['data']['PARAMS']['MESSAGE']),
				Array("MESSAGE" => "latency: ".$latency)
			)
		), $_REQUEST["auth"]);
	}

	// write debug log
	writeToLog($result, 'ImBot Event message add');
}
// receive event "new command for bot"
if ($_REQUEST['event'] == 'ONIMCOMMANDADD')
{
	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
		return false;

	// response time
	$latency = (time()-$_REQUEST['ts']);
	$latency = $latency > 60? (round($latency/60)).'m': $latency."s";

	$result = false;
	foreach ($_REQUEST['data']['COMMAND'] as $command)
	{
		if ($command['COMMAND'] == 'echo')
		{
			$result = restCommand('imbot.command.answer', Array(
				"COMMAND_ID" => $command['COMMAND_ID'],
				"MESSAGE_ID" => $command['MESSAGE_ID'],
				"MESSAGE" => "Answer command",
				"ATTACH" => Array(
					Array("MESSAGE" => "reply: /".$command['COMMAND'].' '.$command['COMMAND_PARAMS']),
					Array("MESSAGE" => "latency: ".$latency),
				)
			), $_REQUEST["auth"]);
		}
		else if ($command['COMMAND'] == 'echoList')
		{
			$initList = false;
			if (!$command['COMMAND_PARAMS'])
			{
				$initList = true;
				$command['COMMAND_PARAMS'] = 1;
			}

			$attach = Array();
			if ($command['COMMAND_PARAMS'] == 1)
			{
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "RED","DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#df532d","COLOR" => "#df532d","DISPLAY" => "LINE"),
				));
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "GRAPHITE", "DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#3a403e", "COLOR" => "#3a403e", "DISPLAY" => "LINE"),
				));
			}
			else if ($command['COMMAND_PARAMS'] == 2)
			{
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "MINT","DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#4ba984","COLOR" => "#4ba984","DISPLAY" => "LINE"),
				));
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "LIGHT BLUE", "DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#6fc8e5", "COLOR" => "#6fc8e5", "DISPLAY" => "LINE"),
				));
			}
			else if ($command['COMMAND_PARAMS'] == 3)
			{
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "PURPLE","DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#8474c8","COLOR" => "#8474c8","DISPLAY" => "LINE"),
				));
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "AQUA", "DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#1eb4aa", "COLOR" => "#1eb4aa", "DISPLAY" => "LINE"),
				));
			}
			else if ($command['COMMAND_PARAMS'] == 4)
			{
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "PINK","DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#e98fa6","COLOR" => "#e98fa6","DISPLAY" => "LINE"),
				));
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "LIME", "DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#85cb7b", "COLOR" => "#85cb7b", "DISPLAY" => "LINE"),
				));
			}
			else if ($command['COMMAND_PARAMS'] == 5)
			{
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "AZURE","DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#29619b","COLOR" => "#29619b","DISPLAY" => "LINE"),
				));
				$attach[] = Array("GRID" => Array(
					Array("VALUE" => "ORANGE", "DISPLAY" => "LINE", "WIDTH" => 100),
					Array("VALUE" => "#e8a441", "COLOR" => "#e8a441", "DISPLAY" => "LINE"),
				));
			}
			$keyboard = Array(
				Array("TEXT" => $command['COMMAND_PARAMS'] == 1? "· 1 ·": "1", "COMMAND" => "echoList", "COMMAND_PARAMS" => "1", "DISPLAY" => "LINE", "BLOCK" => "Y"),
				Array("TEXT" => $command['COMMAND_PARAMS'] == 2? "· 2 ·": "2", "COMMAND" => "echoList", "COMMAND_PARAMS" => "2", "DISPLAY" => "LINE", "BLOCK" => "Y"),
				Array("TEXT" => $command['COMMAND_PARAMS'] == 3? "· 3 ·": "3", "COMMAND" => "echoList", "COMMAND_PARAMS" => "3", "DISPLAY" => "LINE", "BLOCK" => "Y"),
				Array("TEXT" => $command['COMMAND_PARAMS'] == 4? "· 4 ·": "4", "COMMAND" => "echoList", "COMMAND_PARAMS" => "4", "DISPLAY" => "LINE", "BLOCK" => "Y"),
				Array("TEXT" => $command['COMMAND_PARAMS'] == 5? "· 5 ·": "5", "COMMAND" => "echoList", "COMMAND_PARAMS" => "5", "DISPLAY" => "LINE", "BLOCK" => "Y"),
			);

			if (!$initList && $command['COMMAND_CONTEXT'] == 'KEYBOARD')
			{
				$result = restCommand('imbot.message.update', Array(
					"BOT_ID" => $command['BOT_ID'],
					"MESSAGE_ID" => $command['MESSAGE_ID'],
					"ATTACH" => $attach,
					"KEYBOARD" => $keyboard
				), $_REQUEST["auth"]);
			}
			else
			{
				$result = restCommand('imbot.command.answer', Array(
					"COMMAND_ID" => $command['COMMAND_ID'],
					"MESSAGE_ID" => $command['MESSAGE_ID'],
					"MESSAGE" => "List of colors",
					"ATTACH" => $attach,
					"KEYBOARD" => $keyboard
				), $_REQUEST["auth"]);
			}
		}
		else if ($command['COMMAND'] == 'help')
		{
			$keyboard = Array(
				Array(
					"TEXT" => "Bitrix24",
					'LINK' => "http://bitrix24.com",
					"BG_COLOR" => "#29619b",
					"TEXT_COLOR" => "#fff",
					"DISPLAY" => "LINE",
				),
				Array(
					"TEXT" => "BitBucket",
					"LINK" => "https://bitbucket.org/Bitrix24com/rest-bot-echotest",
					"BG_COLOR" => "#2a4c7c",
					"TEXT_COLOR" => "#fff",
					"DISPLAY" => "LINE",
				),
				Array("TYPE" => "NEWLINE"),
				Array("TEXT" => "Echo", "COMMAND" => "echo", "COMMAND_PARAMS" => "test from keyboard", "DISPLAY" => "LINE"),
				Array("TEXT" => "List", "COMMAND" => "echoList", "DISPLAY" => "LINE"),
				Array("TEXT" => "Help", "COMMAND" => "help", "DISPLAY" => "LINE"),
			);

			$result = restCommand('imbot.command.answer', Array(
				"COMMAND_ID" => $command['COMMAND_ID'],
				"MESSAGE_ID" => $command['MESSAGE_ID'],
				"MESSAGE" => "Hello! My name is EchoBot :)[br] I designed to answer your questions!",
				"KEYBOARD" => $keyboard
			), $_REQUEST["auth"]);
		}
	}

	// write debug log
	writeToLog($result, 'ImBot Event message add');
}
// receive event "open private dialog with bot" or "join bot to group chat"
else if ($_REQUEST['event'] == 'ONIMBOTJOINCHAT')
{
	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
		return false;

	if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] == 'LINES')
	{
		$message =
			'ITR Menu:[br]'.
			'[send=1]1. find out more about me[/send][br]'.
			'[send=0]0. wait for operator response[/send]';

		// send help message how to use chat-bot. For private chat and for group chat need send different instructions.
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => $message,
		), $_REQUEST["auth"]);
	}
	else
	{
		// send help message how to use chat-bot. For private chat and for group chat need send different instructions.
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => "Welcome message from bot.",
			"ATTACH" => Array(
				Array("MESSAGE" => ($_REQUEST['data']['PARAMS']['CHAT_TYPE'] == 'P'? 'Private instructions': 'Chat instructions')),
				Array("MESSAGE" => ($_REQUEST['data']['PARAMS']['CHAT_TYPE'] == 'P'? '[send=send message]Click for send[/send] or [put=something...]write something[/put]': "[send=send message]click for send[/send] or [put=put message to textarea]click for put[/put]")),
			),
			"KEYBOARD" => Array(
				Array("TEXT" => "Help", "COMMAND" => "help"),
			)
		), $_REQUEST["auth"]);
	}


	// write debug log
	writeToLog($result, 'ImBot Event join chat');
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
// execute custom action
else if ($_REQUEST['event'] == 'PUBLISH')
{
    // This event is a CUSTOM event and is not sent from platform Bitrix24
	// Example: https://example.com/bot.php?event=PUBLISH&application_token=XXX&PARAMS[DIALOG_ID]=1&PARAMS[MESSAGE]=Hello!
	// example.com - change to you domain name with bot script
	// XXX - change to you application token from config.php

	// check the event - authorize this event or not
	if (!isset($appsConfig[$_REQUEST['application_token']]))
		return false;

	// send answer message
	$result = restCommand('imbot.message.add', $_REQUEST['PARAMS'], $appsConfig[$_REQUEST['application_token']]['AUTH']);

	// write debug log
	writeToLog($result, 'ImBot Event message add');

	echo 'Method executed';
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
		'CODE' => 'echobot',
		'TYPE' => 'B',
		'EVENT_MESSAGE_ADD' => $handlerBackUrl,
		'EVENT_WELCOME_MESSAGE' => $handlerBackUrl,
		'EVENT_BOT_DELETE' => $handlerBackUrl,
		'OPENLINE' => 'Y', // this flag only for Open Channel mode http://bitrix24.ru/~bot-itr
		'PROPERTIES' => Array(
			'NAME' => 'EchoBot '.(count($appsConfig)+1),
			'COLOR' => 'GREEN',
			'EMAIL' => 'test@test.ru',
			'PERSONAL_BIRTHDAY' => '2016-03-11',
			'WORK_POSITION' => 'My first echo bot',
			'PERSONAL_WWW' => 'http://bitrix24.com',
			'PERSONAL_GENDER' => 'M',
			'PERSONAL_PHOTO' => base64_encode(file_get_contents(__DIR__.'/avatar.png')),
		)
	), $_REQUEST["auth"]);
	$botId = $result['result'];

	$result = restCommand('imbot.command.register', Array(
		'BOT_ID' => $botId,
		'COMMAND' => 'echo',
		'COMMON' => 'Y',
		'HIDDEN' => 'N',
		'EXTRANET_SUPPORT' => 'N',
		'LANG' => Array(
			Array('LANGUAGE_ID' => 'en', 'TITLE' => 'Get echo message', 'PARAMS' => 'some text'),
		),
		'EVENT_COMMAND_ADD' => $handlerBackUrl,
	), $_REQUEST["auth"]);
	$commandEcho = $result['result'];

	$result = restCommand('imbot.command.register', Array(
		'BOT_ID' => $botId,
		'COMMAND' => 'echoList',
		'COMMON' => 'N',
		'HIDDEN' => 'N',
		'EXTRANET_SUPPORT' => 'N',
		'LANG' => Array(
			Array('LANGUAGE_ID' => 'en', 'TITLE' => 'Get list of colors', 'PARAMS' => ''),
		),
		'EVENT_COMMAND_ADD' => $handlerBackUrl,
	), $_REQUEST["auth"]);
	$commandList = $result['result'];

	$result = restCommand('imbot.command.register', Array(
		'BOT_ID' => $botId,
		'COMMAND' => 'help',
		'COMMON' => 'N',
		'HIDDEN' => 'N',
		'EXTRANET_SUPPORT' => 'N',
		'LANG' => Array(
			Array('LANGUAGE_ID' => 'en', 'TITLE' => 'Get help message', 'PARAMS' => 'some text'),
		),
		'EVENT_COMMAND_ADD' => $handlerBackUrl,
	), $_REQUEST["auth"]);
	$commandHelp = $result['result'];

	$result = restCommand('event.bind', Array(
		'EVENT' => 'OnAppUpdate',
		'HANDLER' => $handlerBackUrl
	), $_REQUEST["auth"]);

	// save params
	$appsConfig[$_REQUEST['auth']['application_token']] = Array(
		'BOT_ID' => $botId,
		'COMMAND_ECHO' => $commandEcho,
		'COMMAND_HELP' => $commandHelp,
		'COMMAND_LIST' => $commandList,
		'LANGUAGE_ID' => $_REQUEST['data']['LANGUAGE_ID'],
		'AUTH' => $_REQUEST['auth'],
	);
	saveParams($appsConfig);

	// write debug log
	writeToLog(Array($botId, $commandEcho, $commandHelp, $commandList), 'ImBot register');
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