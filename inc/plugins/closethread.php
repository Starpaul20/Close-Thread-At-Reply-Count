<?php
/**
 * Close Thread At Reply Count
 * Copyright 2009 Starpaul20
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Tell MyBB when to run the hooks
$plugins->add_hook("newreply_do_newreply_end", "closethread_run");

// The information that shows up on the plugin manager
function closethread_info()
{
	global $lang;
	$lang->load("closethread", true);

	return array(
		"name"				=> $lang->closethread_info_name,
		"description"		=> $lang->closethread_info_desc,
		"website"			=> "http://galaxiesrealm.com/index.php",
		"author"			=> "Starpaul20",
		"authorsite"		=> "http://galaxiesrealm.com/index.php",
		"version"			=> "1.1",
		"codename"			=> "closethread",
		"compatibility"		=> "18*"
	);
}

// This function runs when the plugin is activated.
function closethread_activate()
{
	global $db;
	$query = $db->simple_select("settinggroups", "gid", "name='posting'");
	$gid = $db->fetch_field($query, "gid");

	$insertarray = array(
		'name' => 'maxreplycount',
		'title' => 'Maximum Number of Replies',
		'description' => 'The maximum amount of replies that can be added to a thread before it is closed. 0 for unlimited.',
		'optionscode' => 'numeric
min=0',
		'value' => 1500,
		'disporder' => 23,
		'gid' => (int)$gid
	);
	$db->insert_query("settings", $insertarray);

	rebuild_settings();
}

// This function runs when the plugin is deactivated.
function closethread_deactivate()
{
	global $db;
	$db->delete_query("settings", "name IN('maxreplycount')");

	rebuild_settings();
}

// Closes the thread if it has more replies than setting
function closethread_run()
{
	global $mybb, $db;
	if($mybb->settings['maxreplycount'] > 0)
	{
		$tid = $mybb->get_input('tid', MyBB::INPUT_INT);
		$query = $db->simple_select("posts", "COUNT(*) AS max_replies", "tid='{$tid}'");
		$reply_count = $db->fetch_field($query, "max_replies");

		if($reply_count-1 >= (int)$mybb->settings['maxreplycount'])
		{
			$closethread = array(
				"closed" => 1,
			);
			$db->update_query("threads", $closethread, "tid='{$tid}'");
		}
	}
}

?>