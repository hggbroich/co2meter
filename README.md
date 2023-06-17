# CO2-Meter InfluxDB

Mithilfe dieses Mini-Programms können die CO₂-Meter in regelmäßigen Abständen ausgelesen werden. Die Informationen 
CO₂-Konzentration, Temperatur und Luftfeuchtigkeit werden dann in eine InfluxDB geschrieben, sodass diese anschließend
bspw. mit Grafana visualisiert werden können.

## Voraussetzungen

* PHP 8.2
* Composer
* Git
* Webserver (bspw. nginx)
* MariaDB
* InfluxDB

Der Server, auf dem dieses Programm läuft, muss natürlich auch Zugriff auf die Geräte haben.

## Funktionsweise

Dem Tool werden zunächst IP-Netzwerke und die MAC-Adressen sowie Räume der einzelnen CO₂-Messgeräte bekannt gemacht
(über die Weboberfläche). 

Im Hintergrund laufen zwei Befehle: `app:scan` und `app:crawl`, welche mittels Supervisior (bspw. `systemd`) gesteuert werden.

Mit dem Befehl `app:scan` werden die angegebenen IP-Netzwerke gescannt und so die IP-Adressen der hinterlegten Geräte
herausgefunden. Da sich diese ändern können, wird der Befehl in regelmäßigen Abständen (bspw. 30min) ausgeführt. Für ein
/24-Netzwerk benötigt der Befehl ca. 5min (leider läuft das Scannen nicht parallel).

Mit dem Befehl `app:crawl` werden alle Geräte abgefragt, deren IP-Adresse bekannt ist. Ist das Gerät nicht erreichbar,
wird eine entsprechende Meldung ausgegeben und das Gerät wird ignoriert. Ist das Gerät online, so werden die Werte (CO2-Konzentration,
Temperatur, Luftfeuchtigkeit) ausgelesen und in eine InfluxDB gespeichert.

Datenpunkte sind dabei folgendermaßen aufgebaut:
* measurement: `sensor`
* tag `room`: Name des Raums
* field `co2`: CO2-Konzentration
* field `temp`: Temperatur
* field `humidity`: Luftfeuchtigkeit

## Installation

```bash
$ cd /srv/http
$ git clone https://github.com/hggbroich/co2meter.git
$ cd co2meter
$ composer install --no-dev --optimize-autoloader
$ cp .env .env.local
```

Nun mittels Texteditor die Konfigurationsdatei (`.env.local`) entsprechend anpassen und anschließend die Datenbank erstellen:

```bash
$ php bin/console doctrine:migrations:migrate
```

Anschließend kann auf die Web-Oberfläche zugegriffen werden. Dazu muss der Webserver entsprechend konfiguriert werden.

Eine Anleitung findet sich in der [Dokumentation von Symfony](https://symfony.com/doc/current/setup/web_server_configuration.html).

## Konfiguration

Die Konfigurationsoberfläche erfordert keine Authentifizierung. Bei Bedarf kann eine Basic-Authentifizierung über den
Webserver realisiert werden.

Anschließend navigiert man zu der Server-Adresse und konfiguriert alle IP-Netzwerke bzw. Geräte.

### systemd-Konfiguration

#### /etc/systemd/system/co2-scan.timer
```
[Unit]
Description=Scanne IP-Netzwerke fuer CO2-Messgeraete

[Timer]
# Alle 30 Minuten ausführen
OnCalendar=*:0/30
Unit=co2-scan.service

[Install]
WantedBy=multi-user.target
```

#### /etc/systemd/system/co2-scan.service

```
[Unit]
Description=Scanne IP-Netzwerke fuer CO2-Messgeraete

[Service]
Type=simple
WorkingDirectory=/srv/http/co2meter
ExecStart=/usr/bin/php8.2 bin/console app:scan
```

#### /etc/systemd/system/co2-crawl.timer
```
[Unit]
Description=Schreibe Werte aus CO2-Messgeraeten in InfluxDB

[Timer]
# Alle 30 Sekunden ausführen
OnCalendar=*:*:0/30
Unit=co2-crawl.service

[Install]
WantedBy=multi-user.target
```

#### /etc/systemd/system/co2-crawl.service

```
[Unit]
Description=Schreibe Werte aus CO2-Messgeraeten in InfluxDB

[Service]
Type=simple
WorkingDirectory=/srv/http/co2meter
ExecStart=/usr/bin/php8.2 bin/console app:crawl
```

