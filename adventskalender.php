<?php
define('IN_MYBB', 1);
require_once './global.php';

global $db, $cache, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $adventcalendar, $calendar_day;

$lang->load('adventcalendar');

// Plugin ist nicht installiert - Zurück auf dem Index
if (!$db->table_exists("adventcalendar")) {
    redirect('index.php', $lang->adventcalendar_redirect_uninstall);
}

// EINSTELLUNGEN
$adventcalendar_activate_setting = $mybb->settings['adventcalendar_activate'];
$adventcalendar_shuffle_setting = $mybb->settings['adventcalendar_shuffle'];
$adventcalendar_formation_setting = $mybb->settings['adventcalendar_formation'];
$adventcalendar_allow_groups = $mybb->settings['adventcalendar_allow_groups'];
$adventcalendar_teamgroups = $mybb->settings['adventcalendar_teamgroup'];

// ZEITZONE - HEUTIGEN TAG
date_default_timezone_set("Europe/Berlin");
$timestamp = time();
$datum = date("j",$timestamp);

// DIE HAUPTSEITE VOM DER DATENBANK
if(!$mybb->input['tuer']) {
    
    if ($adventcalendar_shuffle_setting == 0) {
        $days = explode (", ", $adventcalendar_formation_setting);
    } else {
        $days = range(1, 24);
        shuffle($days);
    }

    // NAVIGATION
    add_breadcrumb("Adventskalender", "adventskalender.php");

    if(!is_member($adventcalendar_allow_groups)) { 
        error_no_permission();
        return;
    }

    // EINZELNEN TAGE DURCHGEHEN
    foreach ($days as $day) {
        if ($datum >= $day) {
            $option = "open";
            $link = "<a href=\"adventskalender.php?tuer={$day}\">{$day}</a>";
        } else {
            $option = "closed";
            if (is_member($adventcalendar_teamgroups)) {
                $option = "";
                $link = "<a href=\"adventskalender.php?tuer={$day}\">{$day}</a>";
            } else {
                $link = "{$day}";
            }
        }
        eval("\$calendar_day .= \"" . $templates->get ("adventcalendar_calendar_day") . "\";");
    }

    // Kalender ist aktiv
    if($adventcalendar_activate_setting == 1) { 
        eval("\$adventcalendar .= \"" . $templates->get ("adventcalendar_calendar") . "\";");
    } else {
        // Teamgruppen können den Kalender immer sehen
        if (is_member($adventcalendar_teamgroups)) {
            eval("\$adventcalendar .= \"" . $templates->get ("adventcalendar_calendar") . "\";");
        } else {
            $adventcalendar = $lang->adventcalendar_deactivate;
        }
    }

    eval("\$page = \"".$templates->get("adventcalendar_mainpage")."\";");
    output_page($page);
    die();
}

$days = explode (", ", "1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24");
foreach ($days as $day) {

    if($mybb->input['tuer'] == "$day") {

        if(!is_member($adventcalendar_allow_groups)) { 
            error_no_permission();
            return;
        }

        // Format Entries
        require_once MYBB_ROOT."inc/class_parser.php";
        $parser = new postParser;
        $parser_options = array(
            "allow_html" => 1,
            "allow_mycode" => 1,
            "allow_smilies" => 1,
            "allow_imgcode" => 1
        );

        // NAVIGATION
        add_breadcrumb($lang->adventcalendar_name, "adventskalender.php");
        $nav_door = $lang->sprintf($lang->adventcalendar_door_nav, $day);
        add_breadcrumb($nav_door, "adventskalender.php?tuer=".$day);

        $claim_day ="";
        // Kontrolliere, ob jeder Tag schon erstellt wurde
        $days_query = $db->query("SELECT day FROM ".TABLE_PREFIX."adventcalendar");
        while ($days = $db->fetch_array($days_query)){
            $claim = "";
            $claim = $days["day"];
            $claim_day .= $claim;
        }

        // Wenn der Tag schon vorhanden ist, dann wird der Inhalt ausgegeben
        if (strpos($claim_day, $day) !== FALSE) {

            $door_query = $db->query("SELECT * FROM ".TABLE_PREFIX."adventcalendar ac
            WHERE day = '$day'
            ");

            while ($door = $db->fetch_array ($door_query)) {
                // LEER LAUFEN LASSEN
                $aid = "";
                $day = "";       
                $text = "";

                // MIT INHALT FÜLLEN
                $aid = $door['aid'];
                $day = $door['day'];

                // heute ist gleich oder größer als der Tag - dann Inhalt
                if ($datum >= $day) {
                    $text = $parser->parse_message($door['text'], $parser_options);
                } 
                // heute ist nicht gleich oder kleiner als der Tag - dann kein Inhalt
                else {
                    if (is_member($adventcalendar_teamgroups)) {
                        $text = $parser->parse_message($door['text'], $parser_options);
                    } else {
                        $text = $lang->sprintf($lang->adventcalendar_notday, $day, $datum);
                    }
                }
            }
       
        } else {
            // Wenn der Tag nicht vorhanden ist, dann dann gibt es eine Meldung!
            if ($datum >= $day) {
                $text = $lang->adventcalendar_nottext;
            } else {
                if (is_member($adventcalendar_teamgroups)) {
                    $text = $lang->adventcalendar_nottext;
                } else {
                    $text = $lang->sprintf($lang->adventcalendar_notday, $day, $datum);
                }
            }
       
        }

        // Kalender ist aktiv - Man sieht den Inhalt
        if($adventcalendar_activate_setting == 1) { 
            eval("\$page = \"".$templates->get("adventcalendar_door")."\";");
        } else {
            // Teamgruppen können den Kalender immer sehen - Man sieht den Inhalt
            if (is_member($adventcalendar_teamgroups)) {
                eval("\$page = \"".$templates->get("adventcalendar_door")."\";");
            } 
            // Alle anderen sehen die Meldung - Kalender deaktiviert
            else {
                $adventcalendar = $lang->adventcalendar_deactivate;
                eval("\$page = \"".$templates->get("adventcalendar_mainpage")."\";");
            }
        }
        output_page($page);
        die();
    }

}
