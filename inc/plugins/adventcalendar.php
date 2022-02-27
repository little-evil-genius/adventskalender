<?php
// Direktzugriff auf die Datei aus Sicherheitsgründen sperren
if(!defined("IN_MYBB")){
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
 
// Die Informationen, die im Pluginmanager angezeigt werden
function adventcalendar_info(){
    global $lang;
    $lang->load('adventcalendar');

	return array(
		"name"		=> $lang->adventcalendar_name,
		"description"	=> $lang->adventcalendar_short_desc,
		"website"	=> "https://github.com/little-evil-genius/adventskalender",
		"author"	=> "little.evil.genius",
		"authorsite"	=> "https://storming-gates.de/member.php?action=profile&uid=1712",
		"version"	=> "1.0",
		"compatibility" => "18*"
	);
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin installiert wird (optional).
function adventcalendar_install(){
    global $db, $cache, $mybb;

    // DATENBANK HINZUFÜGEN
    $db->query("CREATE TABLE ".TABLE_PREFIX."adventcalendar(
        `aid` int(10) NOT NULL AUTO_INCREMENT,
        `day` int(10) NOT NULL,
        `title` VARCHAR(1000),
        `text` VARCHAR(500000),
        PRIMARY KEY(`aid`),
        KEY `aid` (`aid`)
        )
        ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
    ");

	// EINSTELLUNGEN HINZUFÜGEN
	$setting_group = array(
		'name'          => 'adventcalendar',
		'title'         => 'Adventskalender',
		'description'   => 'Einstellungen für die Adventskalender',
		'disporder'     => 1,
		'isdefault'     => 0
	);
			
	$gid = $db->insert_query("settinggroups", $setting_group); 
			
	$setting_array = array(
	    // Plugin aktiv
		'adventcalendar_activate' => array(
			'title' => 'Plugin aktivieren',
			'description' => 'Soll der Adventskalender aktiv sein?',
			'optionscode' => 'yesno',
			'value' => '0', // Default
			'disporder' => 1
		),
		// Erlaubte Gruppen
		'adventcalendar_allow_groups' => array(
			'title' => 'Erlaubte Gruppen',
			'description' => 'Welche Gruppen dürfen den Adventskalender sehen?',
			'optionscode' => 'groupselect',
			'value' => '4', // Default
			'disporder' => 2
		),
        // Shuffle Modus
		'adventcalendar_shuffle' => array(
			'title' => 'Anordnung zufällig generieren?',
			'description' => 'Sollen die Anordnung der Tage bei jedem neu laden der Seite zufällig generiert werden?',
			'optionscode' => 'yesno',
			'value' => '0', // Default
			'disporder' => 3
		),
        // Manuelle Anordnung
		'adventcalendar_formation' => array(
			'title' => 'Anordnung der Tage',
			'description' => 'In welcher Reihenfolge, sollen die Tage ausgegeben werden?',
			'optionscode' => 'text',
			'value' => '10, 4, 11, 7, 23, 1, 5, 9, 20, 15, 2, 3, 18, 12, 6, 24, 16, 17, 22, 19, 8, 14, 21, 13', // Default
			'disporder' => 4
		),
        // Teamgruppen
        'adventcalendar_teamgroup' => array(
            'title' => 'Teamgruppen',
            'description' => 'Welche Gruppen darf den Adventskalender immer sehen, selbst wenn er nicht aktiv ist? Auch sehen diese Gruppenmitglieder immer jeden Tag, egal ob dieser schon offen ist oder nicht!',
            'optionscode' => 'groupselect',
            'value' => '4', // Default
            'disporder' => 5
        ),
	);
			
	foreach($setting_array as $name => $setting)
	{
		$setting['name'] = $name;
		$setting['gid']  = $gid;
		$db->insert_query('settings', $setting);
	}
	rebuild_settings();

    // TEMPLATES HINZUFÜGEN
    $insert_array = array(
        'title' => 'adventcalendar_mainpage',
        'template' => $db->escape_string('<html>    
        <head>
        <title>{$settings[\'bbname\']} - {$lang->adventcalendar_name}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <table width="100%"  border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
         <tr> 
            <td class="thead">{$lang->adventcalendar_name}</td>
         </tr>
         <tr>
            <td>
                {$adventcalender}
            </td>
         </tr>
        </table>
        {$footer}
        </body>
        </html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW    
    );    
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'adventcalendar_calender_day',
        'template' => $db->escape_string('<div class="tuerchen {$option}">
        <div class="zahl">
           {$link}
         </div>	
       </div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW    
    );    
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'adventcalendar_calendar',
        'template' => $db->escape_string('<div id="adventskalender">
        <div class="kalenderbox">
            {$calendar_day}
        </div>    
    </div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW    
    );    
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'adventcalendar_door',
        'template' => $db->escape_string('<html>
        <head>
        <title>{$settings[\'bbname\']} - {$lang->adventcalendar_name}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <table width="100%"  border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
        <tr> 
                <td class="thead">{$lang->adventcalendar_door} {$day}</td>
            </tr>
         <tr>
             <td>
        {$text}
             </td>
            </tr>
        </table>
        {$footer}
        </body>
        </html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW    
    );    
    $db->insert_query("templates", $insert_array);

    // CSS HINZUFÜGEN
    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

    // STYLESHEET HINZUFÜGEN
    $css = array(
		'name' => 'adventcalendar.css',
		'tid' => 1,
		'attachedto' => '',
		"stylesheet" =>	'#adventskalender {
	width: 100%;
	background-image: url(../../../images/adventskalender/background.jpg);
	background-repeat: repeat;
	background-position: center center;
	box-sizing: border-box;
	display: inline-block;
	background-size: 100% auto;
}

#adventskalender .kalenderbox {
	padding: 31px;
	height: auto;
	width: 100%;
	box-sizing: border-box;
	display: inline-block;
}
        
#adventskalender .kalenderbox .tuerchen {
	float: left;
	box-sizing: border-box;
	background: transparent;
	width: 16.6%;
}
        
#adventskalender .kalenderbox .tuerchen .zahl {
	border: 2px dashed #d7b587;
	border-left: 2px solid #7d8873;
	background-color: #285d61;
	position: relative;
	z-index: 2;
	display: block;
	background-repeat: no-repeat;
	text-align: center;
	margin: 5%;
	height: 50%;
	line-height: 130px;
	font-size: 27px;
	opacity: 01;
	color: #d7b587;
}
        
#adventskalender .kalenderbox .tuerchen .zahl a:link,
#adventskalender .kalenderbox .tuerchen .zahl a:visited,
#adventskalender .kalenderbox .tuerchen .zahl a:hover,
#adventskalender .kalenderbox .tuerchen .zahl a:active{
	color: #d7b587;
}
        
#adventskalender .kalenderbox .open {
	right: 1%;
	position: relative;
	transform: perspective(800px) rotateY(-30deg) translate(-7px,0px);
}
        
#adventskalender .kalenderbox .open:before {
	content: "";
	position: absolute;
	top: 9%;
	right: -7%;
	bottom: 8%;
	left: 5%;
	background-color: rgb(255 255 255 / 45%);
	z-index: 1;
}
        
#adventskalender .kalenderbox .closed:hover {
	animation: shake .5s ease-in-out;    
}
        
@keyframes shake {
	0% {
		transform: translateX(0);
	}
	
	20% {
		transform: translateX(-10px);
	}
	
	40% {
		transform: translateX(10px);
	}
	
	60% {
		transform: translateX(-10px);
	}
	
	80% {
		transform: translateX(10px);
	}
	
	100% {
		transform: translateX(0);
	}
}',
		'cachefile' => $db->escape_string(str_replace('/', '', 'adventcalendar.css')),
		'lastmodified' => time()
	);
    
    $sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=".$sid), "sid = '".$sid."'", 1);

	$tids = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($tids)) {
		update_theme_stylesheet_list($theme['tid']);
	}
}
 
// Funktion zur Überprüfung des Installationsstatus; liefert true zurürck, wenn Plugin installiert, sonst false (optional).
function adventcalendar_is_installed(){
	global $db, $mybb;

    if ($db->table_exists("adventcalendar")) {
        return true;
    }
    return false;
} 
 
// Diese Funktion wird aufgerufen, wenn das Plugin deinstalliert wird (optional).
function adventcalendar_uninstall(){
	global $db;

    //DATENBANK LÖSCHEN
    if($db->table_exists("adventcalendar"))
    {
        $db->drop_table("adventcalendar");
    }
    
    // EINSTELLUNGEN LÖSCHEN
    $db->delete_query('settings', "name LIKE 'adventcalendar%'");
    $db->delete_query('settinggroups', "name = 'adventcalendar'");

    rebuild_settings();

    // TEMPLATES LÖSCHEN
    $db->delete_query("templates", "title LIKE '%adventcalendar%'");

    // CSS LÖSCHEN
    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

    $db->delete_query("themestylesheets", "name = 'adventcalendar.css'");
	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query)) {
		update_theme_stylesheet_list($theme['tid']);
	}

}

// ADMIN-CP PEEKER
$plugins->add_hook('admin_config_settings_change', 'adventcalendar_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'adventcalendar_settings_peek');
function adventcalendar_settings_change(){
    global $db, $mybb, $adventcalendar_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='adventcalendar'", array("limit" => 1));
    $group = $db->fetch_array($result);
    $adventcalendar_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}
function adventcalendar_settings_peek(&$peekers){
    global $mybb, $adventcalendar_settings_peeker;

    if ($adventcalendar_settings_peeker) {
       $peekers[] = 'new Peeker($(".setting_adventcalendar_shuffle"), $("#row_setting_adventcalendar_formation"),/0/,true)';
    }
}

// action handler fürs acp konfigurieren
$plugins->add_hook("admin_config_action_handler", "adventcalendar_admin_config_action_handler");
function adventcalendar_admin_config_action_handler(&$actions){
    $actions['adventcalendar'] = array('active' => 'adventcalendar', 'file' => 'adventcalendar');
}

// Berechtigungen im ACP - Adminrechte
$plugins->add_hook("admin_config_permissions", "adventcalendar_admin_config_permissions");
function adventcalendar_admin_config_permissions(&$admin_permissions){
    global $lang;
    $lang->load('adventcalendar');

    $admin_permissions['adventcalendar'] = $lang->adventcalendar_permission;

    return $admin_permissions;
}

// Menü einfügen
$plugins->add_hook("admin_config_menu", "adventcalendar_admin_config_menu");
function adventcalendar_admin_config_menu(&$sub_menu){
    global $mybb, $lang;
    $lang->load('adventcalendar');
    
    $sub_menu[] = [
        "id" => "adventcalendar",
        "title" => $lang->adventcalendar_manage,
        "link" => "index.php?module=config-adventcalendar"
    ];
}

// Adventskalender verwalten in ACP
$plugins->add_hook("admin_load", "adventcalendar_manage_adventcalendar");
function adventcalendar_manage_adventcalendar() {

    global $mybb, $db, $lang, $page, $run_module, $action_file;

    $lang->load('adventcalendar');

    if ($page->active_action != 'adventcalendar') {
        return false;
    }

    // Add to page navigation
    $page->add_breadcrumb_item($lang->adventcalendar_manage);

    if ($run_module == 'config' && $action_file == 'adventcalendar') {
        // Adventskalender Übersicht
        if ($mybb->input['action'] == "" || !isset($mybb->input['action'])) {

            // Optionen im Header bilden
            $page->output_header($lang->adventcalendar_manage." - ".$lang->adventcalendar_manage_overview_entries);

            // Übersichtsseite Button
            $sub_tabs['adventcalendar'] = [
                "title" => $lang->adventcalendar_manage_overview_entries,
                "link" => "index.php?module=config-adventcalendar",
                "description" => $lang->adventcalendar_manage_overview_entries_desc
                
            ];
            // Hinzufüge Button
            $sub_tabs['adventcalendar_entry_add'] = [
                "title" => $lang->adventcalendar_manage_add_entry,
                "link" => "index.php?module=config-adventcalendar&amp;action=add_entry",
                "description" => $lang->adventcalendar_manage_add_entry_desc
            ];

            $page->output_nav_tabs($sub_tabs, 'adventcalendar');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Übersichtsseite
            $form = new Form("index.php?module=config-adventcalendar", "post");

            $form_container = new FormContainer($lang->adventcalendar_manage_overview_entries);
            $form_container->output_row_header($lang->adventcalendar_manage_days, array('style' => 'text-align: center; width: 10%;'));
            $form_container->output_row_header($lang->adventcalendar_manage_title);
            $form_container->output_row_header($lang->adventcalendar_manage_options, array('style' => 'text-align: center; width: 20%;'));
            
            // Alle Einträge - nach Tag sortieren
            $query = $db->simple_select("adventcalendar", "*", "",
                ["order_by" => 'day', 'order_dir' => 'ASC']);
 
            while($adventcalendar_entries = $db->fetch_array($query)) {

                $form_container->output_cell('<center><strong>'.htmlspecialchars_uni($adventcalendar_entries['day']).'</strong></center>');
                $form_container->output_cell(htmlspecialchars_uni($adventcalendar_entries['title']));
                $popup = new PopupMenu("adventcalendar_{$adventcalendar_entries['aid']}", $lang->adventcalendar_manage_options);
                $popup->add_item(
                    $lang->adventcalendar_manage_edit,
                    "index.php?module=config-adventcalendar&amp;action=edit_entry&amp;aid={$adventcalendar_entries['aid']}"
                );
                $popup->add_item(
                    $lang->adventcalendar_manage_delete,
                    "index.php?module=config-adventcalendar&amp;action=delete_entry&amp;aid={$adventcalendar_entries['aid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
                $form_container->construct_row();
            }

            $form_container->end();
            $form->end();
            $page->output_footer();

            exit;
        }
        if ($mybb->input['action'] == "add_entry") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['day'])) {
                    $errors[] = $lang->adventcalendar_manage_error_no_title;
                }
                if (empty($mybb->input['text'])) {
                    $errors[] = $lang->adventcalendar_manage_error_no_text;
                }
                if (empty($mybb->input['title'])) {
                    $errors[] = $lang->adventcalendar_manage_error_no_title;
                }

                // No errors - insert
                if (empty($errors)) {

                    $new_entry = array(
                        "day" => $db->escape_string($mybb->input['day']),
                        "title" => $db->escape_string($mybb->input['title']),
                        "text" => $db->escape_string($mybb->input['text'])
                    );

                    $db->insert_query("adventcalendar", $new_entry);

                    $mybb->input['module'] = "adventcalendar";
                    $mybb->input['action'] = $lang->adventcalendar_manage_entry_added;
                    log_admin_action(htmlspecialchars_uni($mybb->input['day']));

                    flash_message($lang->adventcalendar_manage_entry_added, 'success');
                    admin_redirect("index.php?module=config-adventcalendar");
                }
            }
                
                $page->add_breadcrumb_item($lang->adventcalendar_manage_add_entry);
                // Editor scripts
                $page->extra_header .= <<<EOF
	<link rel="stylesheet" href="../jscripts/sceditor/themes/mybb.css" type="text/css" media="all" />
	<script type="text/javascript" src="../jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver=1822"></script>
	<script type="text/javascript" src="../jscripts/bbcodes_sceditor.js?ver=1827"></script>
	<script type="text/javascript" src="../jscripts/sceditor/plugins/undo.js?ver=1805"></script>
EOF;

                // Build options header
                $page->output_header($lang->adventcalendar_manage." - ".$lang->adventcalendar_manage_overview_entries);
                $sub_tabs['adventcalendar'] = [
                    "title" => $lang->adventcalendar_manage_overview_entries,
                    "link" => "index.php?module=config-adventcalendar",
                    "description" => $lang->adventcalendar_manage_overview_entries_desc
                    
                ];
                $sub_tabs['adventcalendar_entry_add'] = [
                    "title" => $lang->adventcalendar_manage_add_entry,
                    "link" => "index.php?module=config-adventcalendar&amp;action=add_entry",
                    "description" => $lang->adventcalendar_manage_add_entry_desc
                ];

                $page->output_nav_tabs($sub_tabs, 'adventcalendar_entry_add'); 

                // Show errors
                if (isset($errors)) {
                    $page->output_inline_error($errors);
                }

                // Build the form
                $form = new Form("index.php?module=config-adventcalendar&amp;action=add_entry", "post", "", 1);
                $form_container = new FormContainer($lang->adventcalendar_manage_add_entry);

                $form_container->output_row(
                    $lang->adventcalendar_manage_entry_day_title."<em>*</em>",
                    $lang->adventcalendar_manage_entry_day_desc,
                    $form->generate_text_box('day', $mybb->input['day'])
                );

                $form_container->output_row(
                    $lang->adventcalendar_manage_entry_title_title."<em>*</em>",
                    $lang->adventcalendar_manage_entry_title_desc,
                    $form->generate_text_box('title', $mybb->input['title'])
                );

                $text_editor = $form->generate_text_area('text', $mybb->input['text'], array(
                    'id' => 'text',
                    'rows' => '25',
                    'cols' => '70',
                    'style' => 'height: 450px; width: 75%'
                    )
                );

                $text_editor .= build_mycode_inserter('text');
                $form_container->output_row(
                    $lang->adventcalendar_manage_entry_text_title. "<em>*</em>",
                    $lang->adventcalendar_manage_entry_text_desc,
                    $text_editor,
                    'text'
                );

                $form_container->end();
                $buttons[] = $form->generate_submit_button($lang->adventcalendar_manage_submit_add);
                $form->output_submit_wrapper($buttons);
                $form->end();
                $page->output_footer();
    
                exit;         
        }
        if ($mybb->input['action'] == "edit_entry") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['day'])) {
                    $errors[] = $lang->adventcalendar_manage_error_no_title;
                }
                if (empty($mybb->input['text'])) {
                    $errors[] = $lang->adventcalendar_manage_error_no_text;
                }
                if (empty($mybb->input['title'])) {
                    $errors[] = $lang->adventcalendar_manage_error_no_title;
                }

                // No errors - insert the terms of use
                if (empty($errors)) {
                    $aid = $mybb->get_input('aid', MyBB::INPUT_INT);

                    $edited_entry = [
                        "day" => $db->escape_string($mybb->input['day']),
                        "text" => $db->escape_string($mybb->input['text']),
                        "title" => $db->escape_string($mybb->input['title'])
                    ];

                    $db->update_query("adventcalendar", $edited_entry, "aid='{$aid}'");

                    $mybb->input['module'] = "adventcalendar";
                    $mybb->input['action'] = $lang->adventcalendar_manage_entry_edited;
                    log_admin_action(htmlspecialchars_uni($mybb->input['day']));

                    flash_message($lang->adventcalendar_manage_entry_edited, 'success');
                    admin_redirect("index.php?module=config-adventcalendar");
                }

            }
            
           $page->add_breadcrumb_item($lang->adventcalendar_manage_edit_entry);

            // Editor scripts
            $page->extra_header .= <<<EOF
<link rel="stylesheet" href="../jscripts/sceditor/themes/mybb.css" type="text/css" media="all" />
<script type="text/javascript" src="../jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver=1822"></script>
<script type="text/javascript" src="../jscripts/bbcodes_sceditor.js?ver=1827"></script>
<script type="text/javascript" src="../jscripts/sceditor/plugins/undo.js?ver=1805"></script>
EOF;

            // Build options header
            $page->output_header($lang->adventcalendar_manage." - ".$lang->adventcalendar_manage_overview_entries);
            $sub_tabs['adventcalendar'] = [
                "title" => $lang->adventcalendar_manage_overview_entries,
                "link" => "index.php?module=config-adventcalendar",
                "description" => $lang->adventcalendar_manage_overview_entries_desc
                
            ];
            $sub_tabs['adventcalendar_entry_add'] = [
                "title" => $lang->adventcalendar_manage_add_entry,
                "link" => "index.php?module=config-adventcalendar&amp;action=add_entry",
                "description" => $lang->adventcalendar_manage_add_entry_desc
            ];
            $sub_tabs['adventcalendar_entry_edit'] = [
                "title" => $lang->adventcalendar_manage_edit_entry,
                "link" => "index.php?module=config-adventcalendar&amp;action=edit_entry",
                "description" => $lang->adventcalendar_manage_edit_entry_desc
            ];

            $page->output_nav_tabs($sub_tabs, 'adventcalendar_entry_edit');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Get the data
            $aid = $mybb->get_input('aid', MyBB::INPUT_INT);
            $query = $db->simple_select("adventcalendar", "*", "aid={$aid}");
            $edit_entry = $db->fetch_array($query);

            // Build the form
            $form = new Form("index.php?module=config-adventcalendar&amp;action=edit_entry", "post", "", 1);
            echo $form->generate_hidden_field('aid', $aid);

            $form_container = new FormContainer($lang->adventcalendar_manage_edit_entry);
            $form_container->output_row(
                $lang->adventcalendar_manage_entry_day_title,
                $lang->adventcalendar_manage_entry_day_desc,
                $form->generate_text_box('day', htmlspecialchars_uni($edit_entry['day']))
            );

            $form_container->output_row(
                $lang->adventcalendar_manage_entry_title_title,
                $lang->adventcalendar_manage_entry_title_desc,
                $form->generate_text_box('title', htmlspecialchars_uni($edit_entry['title']))
            );

            $text_editor = $form->generate_text_area('text', $edit_entry['text'], array(
                    'id' => 'text',
                    'rows' => '25',
                    'cols' => '70',
                    'style' => 'height: 450px; width: 75%'
                )
            );
            $text_editor .= build_mycode_inserter('text');
            $form_container->output_row(
                $lang->adventcalendar_manage_entry_text_title,
                $lang->adventcalendar_manage_entry_text_desc,
                $text_editor,
                'text'
            );
 
            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->adventcalendar_manage_submit_edit);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();

            exit;
        }
       // Delete entry
       if ($mybb->input['action'] == "delete_entry") {
            // Get data
            $aid = $mybb->get_input('aid', MyBB::INPUT_INT);
            $query = $db->simple_select("adventcalendar", "*", "aid={$aid}");
            $del_entry = $db->fetch_array($query);

            // Error Handling
            if (empty($aid)) {
                flash_message($lang->adventcalendar_manage_error_invalid, 'error');
                admin_redirect("index.php?module=config-adventcalendar");
            }

            // Cancel button pressed?
            if (isset($mybb->input['no']) && $mybb->input['no']) {
                admin_redirect("index.php?module=config-adventcalendar");
            }

            if (!verify_post_check($mybb->input['my_post_key'])) {
                flash_message($lang->invalid_post_verify_key2, 'error');
                admin_redirect("index.php?module=config-adventcalendar");
            }  // all fine
            else {
                if ($mybb->request_method == "post") {
                    
                    $db->delete_query("adventcalendar", "aid='{$aid}'");

                    $mybb->input['module'] = "adventcalendar";
                    $mybb->input['action'] = $lang->adventcalendar_manage_entry_deleted;
                    log_admin_action(htmlspecialchars_uni($del_entry['day']));

                    flash_message($lang->adventcalendar_manage_entry_deleted, 'success');
                    admin_redirect("index.php?module=config-adventcalendar");
                } else {
                    $page->output_confirm_action(
                        "index.php?module=config-adventcalendar&amp;action=delete_entry&amp;aid={$aid}",
                        $lang->adventcalendar_manage_delete
                    );
                }
            }
            exit;
        }
    }
}


// ONLINE LOCATION
$plugins->add_hook("fetch_wol_activity_end", "adventcalendar_online_activity");
$plugins->add_hook("build_friendly_wol_location_end", "adventcalendar_online_location");

function adventcalendar_online_activity($user_activity) {
global $parameters, $user;

    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location']) {
        $filename = '';
    } else {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }
    
    switch ($filename) {
        case 'adventskalender':
        if(!isset($parameters['action']))
        {
            $user_activity['activity'] = "haupt";
        }
        if($parameters['action'] == "tuer") {
            $user_activity['activity'] = "tuer";
        }
        break;
    }
      
return $user_activity;
}

function adventcalendare_online_location($plugin_array) {
global $mybb, $theme, $lang;

	if($plugin_array['user_activity']['adventskalender'] == "haupt") {
		$plugin_array['location_name'] = "Sieht sich den <a href=\"adventcalendar.php?action=main\">Adventskalender</a> an.";
	}
    if($plugin_array['user_activity']['adventskalender'] == "tuer") {
		$plugin_array['location_name'] = "Hat ein Türchen am Adventskalender geöffnet.";
	}

return $plugin_array;
}
