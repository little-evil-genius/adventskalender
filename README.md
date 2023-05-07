# Adventskalender
Mit diesem Plugin lassen sich über das AdminCP die Inhalte für den Adventskalender (sämtliche Formatierungsmöglichkeiten erlaubt) erstellen. Diese werden anschließend unter url.de/adventskalender.php aufgelistet. Ausgewählte Gruppen können den Adventskalender und seine Inhalte sehen. Auch kann das Team den Adventskalender bei nicht Benutzung das Plugin über die Einstellung deaktivieren. Ausgewählte Teamgruppen können den Kalender immer sehen, selbst wenn dieser deaktiviert ist und auch die Türen, die noch gar nicht geöffnet werden können. Türchen lassen sich erst öffen, selbst wenn man es über den Link direkt aufrufen will, wenn auch der entsprechende Tag gegeben ist.<br><br>
Der Adventskalender ist komplett mit Divs gecodet worden und kann somit angepasst werden nach Lust und Laune. 

# Neue Templates
- adventcalendar_calendar
- adventcalendar_calendar_day
- adventcalendar_door
- adventcalendar_mainpage

# Neuer Stylesheet 
- adventcalendar.css

# ACP-Einstellungen - Adventskalender
- Plugin aktivieren
- Erlaubte Gruppen
- Anordnung zufällig generieren?
- Anordnung der Tage
- Teamgruppen

# Links
- ACP: BOARDLINK/admin/index.php?module=config-adventcalendar
- Kalender: BOARDLINK/adventskalender.php
- Türchen: BOARDLINK/adventskalender.php?tuer=xx

# Administrator-Berechtigungen
Damit alle Adminaccounts die Verwaltung sehen können, muss im  Admin CP die Berechtigungen noch eingestellt werden. Dafür einmal Benutzer & Gruppen » Administrator-Berechtigungen » Benutzer-Berechtigungen » Standard-Berechtigungen » Tab "Konfiguration", ganz unten
Link: BOARDLINK/admin/index.php?module=user-admin_permissions

# Design
Wie oben schon geschrieben, wurde der komplette Adventskalender ansich per Divs gecodet und kann somit von euch angepasst werden, falls jemand die Default Ansicht nicht möchte. 
Wichtig dabei sind die zwei Tpls <b>adventcalendar_calendar</b> und <b>adventcalendar_calendar_day</b>.<br>
Im tpl adventcalendar_calendar befindet sich der Körper von dem Adventskalender quasi. Und damit man nicht 24 einzelne Boxen für die Tage in diesem Tpl hat (und es dadurch unübersichtlich wird) gibt es die Variable <b>{$calendar_day}</b>. Diese ruft das Tpl adventcalendar_calendar_day auf, wo die einzelnen Tagesboxen definiert werden.<br>
In dem zweiten Tpl befinden sich zwei Variabalen. Aber nur eine ist wirklich wichtig. Mit <b>{$link}</b> bildet sich der Link/die Zahlen in den Fensterchen. Er ist eine Variable, damit wenn der Tag noch gar zu öffnen ist nur die Zahl dort steht und noch kein Link angezeigt wird. Die zweite Variable {$option} ist im Grunde nur eine Spielerei, damit im css dieses offene und geschlossene definiert und zugeordnet werden kann. Also falls ihr daran kein Interesse habt, dann könnt ihr diese Variable auch entfernen. <br>
Falls ihr die Türchen zB jeden Tag eine andere Farbe oder andere Eigenschaften geben wollt, könnt ihr das auch durch die Variable d{$day} erreichen. Setzt <b>d{$day}</b> dort hin, wo ihr spezifisch etwas für diesen Tag anders haben wollt. Zb hinter Zahl. Im CSS müsst ihr dann zb .d2 { background: #red !important; } und schon wird die Zwei in rot dargestellt. Eine genauere Erklärung findet ihr von Jule aka sparks fly im <a href="https://storming-gates.de/showthread.php?tid=1020944">SG</a> oder im <a href="https://epic.quodvide.de/showthread.php?tid=80">Epic</a>.


# Demo 
 ACP Verwaltung<p>
 <img src="https://stormborn.at/plugins/adventskalender_acp_uebersicht.png" />
 
 ACP Hinzufügen<p>
 <img src="https://stormborn.at/plugins/adventskalender_acp_add.png" />
 
Default Adventskalenderanzeige - offene und geschlossene Türchen<p>
 <img src="https://stormborn.at/plugins/adventskalender.png" />

# Credits
Das Hintergrundbild:
<a href='https://www.freepik.com/vectors/christmas-sketch'>Christmas sketch vector created by createvil - www.freepik.com</a>
