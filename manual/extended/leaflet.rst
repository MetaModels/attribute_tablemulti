.. _rst_extended_leaflet:

Leaflet-Maps Integration
########################

Mit der `Leaflet-Maps Integration <https://github.com/netzmacht/contao-leaflet-metamodels>`_ wird die Darstellung von MetaModels in der
Erweiterung `netzmacht/contao-leaflet-maps`_ ermöglicht.

.. note:: Diese Dokumentation bezieht sich ausschließlich auf Contao 4, auch
   wenn die Erweiterung auch für Contao 3.5. bereitgestellt wird.


Funktionen
----------

 * MetaModels Item als Marker auf Karte rendern
 * Im MetaModels Item Layer referenzieren und auf der Karte darstellen
 * Im MetaModels Item GeoJson-Dateien verlinken und auf der Karte darstellen
 * Attribut Leaflet-Karte: Direkt eine Karte im MetaModels Item rendern


Voraussetzungen
---------------

Contao 4
~~~~~~~~

 - min. Contao 4.4
 - min. MetaModels 2.1
 - min. PHP 7.1
 - min. Symfony 3.4

Contao 3.5
~~~~~~~~~~

*(Bugfixsupport auslaufend im Mai 2019)*

 - MetaModels 2.0
 - `netzmacht/contao-leaflet-maps`_ 2.0
 - min. PHP 5.4

Installation
------------

Über Composer/Contao Manager lässt sich `netzmacht/contao-leaflet-metamodels`_ installieren.


MetaModel auf Karte integrieren
-------------------------------

In dieser Anleitung wird gezeigt, wie man ein MetaModels, welches Geokoordinaten
besitzt, auf einer Karte von Leaflet für Contao dargestellt werden kann.


Koordinaten-Attribute
~~~~~~~~~~~~~~~~~~~~~

Die Geokoordinaten können als getrennte Attribute oder in einem Attribut
(Latitude und Longitude mit Komma getrennt) im MetaModel definiert werden.
Als Attributstyp eignet sich z.B. ein einfaches Textattribut.

.. figure:: /_img/screenshots/extended/leaflet/mm_attribute.png
   :alt: Attribute im MetaModel

   Attribute Latitude und Longitude im MetaModel

.. _netzmacht/contao-leaflet-maps: https://github.com/netzmacht/contao-leaflet-maps
.. _netzmacht/contao-leaflet-metamodels: https://github.com/netzmacht/contao-leaflet-metamodels


MetaModels Layer anlegen
~~~~~~~~~~~~~~~~~~~~~~~~

Als nächster Schritt, wird unter Karten-Layer einen neuen Layer vom Typ
"MetaModels" angelegt. Folgende Einstellungen sind hier vorzunehmen:

 * **Typ**: MetaModel auswählen
 * **MetaModel**: Das gewünschte MetaModel
 * **Bounds relation**: Legt fest, welche Abhängigkeiten zwischen den Elementen des Layers
   und den Kartengrenzen bestehen soll - Auswahl von *extend*. Die Kartengrenzen werden durch die
   definierten Marker erweitert.
 * **Anzuwendende Filtereinstellung**: Hier wird, wie bei MetaModels gewohnt, eine Filtereinstellung
   ausgewählt, die die anzuzeigenden Items beeinflusst.

.. figure:: /_img/screenshots/extended/leaflet/leaflet_layer.png
   :alt: Konfiguration des Layers MetaModels

   Konfiguration des Layers MetaModels


MetaModels Layer Renderer anlegen
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Im nächsten Schritt wird definiert, wie das MetaModels Item auf der Karte
dargestellt werden soll. Diese sollen in dem Beispiel als Marker dargestellt werden.
Dazu können über das Bearbeiten-Icon des Karten-Layers die entsprechenden *Renderer*
angelegt werden.

.. figure:: /_img/screenshots/extended/leaflet/leaflet_layer_2.png
   :alt: Übersicht der Karten-Layer

   Übersicht der Karten-Layer

In der Eingabemaske ist es möglich, neue Renderer zu definieren. Folgende Einstellungen sind
hier vorzunehmen:

 * **Typ**: Auswahl von *marker*, da die MetaModel Items als Marker dargestellt werden sollen
 * **Koordinaten**: Auswahl von *separate*, wenn die Werte für Latitude und Longitude in separaten
   Attributen vorliegen
 * **Breite-Attribut**: Auswahl des Attributs für *Latitude* aus
 * **Länge-Attribut**: Auswahl des Attributs für *Longitude* aus
 * **Rendererinstellung aktivieren**: aktivieren der Rendereinstellung
 * **Verzögertes Laden**: Bei größeren Listen empfiehlt sich das dynamische Nachladen der Kartendaten
   über eine API. Diese werden dann nicht direkt als Javascript gerendert.

Zusätzlich zu der Grundkonfiguration, kann das MetaModel auch als Popup zum Marker
hinzugefügt werden. Hier werden zwei Modi unterstützt:

 * **render**: Eine Rendereinstellung wird ausgewählt und gerendert
 * **attribute**: Es wird ein Attribut gerendert. Auch hierfür muss eine Rendererinstellung
   ausgewählt werden

Weiterhin ist es möglich die Darstellung als Icon zu beeinflussen. Es kann eines der
vordefinierten Icons ausgewählt oder Alternativ dazu über ein MetaModels-Attribut
bestimmt werden.

.. figure:: /_img/screenshots/extended/leaflet/layer_renderer.png
   :alt: Einstellung des Renderers

   Einstellung des Renderers


Layer in Karte aktivieren
~~~~~~~~~~~~~~~~~~~~~~~~~

Als letzter Schritt, muss dem Layer für die Darstellung noch eine Karte zugewiesen werden. Dies
kann über die Standardlayer einer Karte erfolgen.

Zudem ist es zu empfehlen, bei der Funktion *Grenzen festlegen* die Optionen *bei Karteninitialisierung* und
*Nach dem Laden des verzögerten Features* zu aktivieren. Damit erweitert sich die Karte dynamisch um den
Bereich, indem die Marker existieren.

.. figure:: /_img/screenshots/extended/leaflet/leaflet_map.png
   :alt: Karteneinstellungen

   Karteneinstellungen

Ist auf der Seite ein Filter eingebunden der die oben ausgewählte Filtereinstellung
bedient, wird die Kartenansicht entsprechend gefiltert.
