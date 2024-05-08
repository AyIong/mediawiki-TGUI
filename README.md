# TGUI Skin

## Installation

Download all these files into a folder name "TGUI"

Place the TGUI folder into Mediawiki/Skins (Or, clone this repo into the skin folder, and rename cloned folder into "TGUI")

go to MediaWiki LocalSetting.php and add this line `wfLoadSkin( 'TGUI' );` next to all the other wfLoadSkin() calls

You may need to reload Mediawiki for it to show up.

Now when users go to their Special:Preferences page, they can change their appearance setting theme to TGUI

## Uninstall

REQUIRED: remove this line `wfLoadSkin( 'TGUI' );` from LocalSetting.php
REQUIRED: If you set $wgDefaultSkin to 'TGUI' you must switch it to another skin you have installed

MediaWiki will automatically switch users using TGUI back to the default skin

You can keep the TGUI folder in Skins if you just want to disable its use for the time being without
any issues. Otherwise you can just delete it at this point.

### Configuration options

WiP.

### Development

Most of the Template/Skin PHP files is sourced from the Vector Skin(I did a bit of customization) so credit goes to everyone who worked on that.

### Coding conventions

We strive for compliance with MediaWiki conventions:

<https://www.mediawiki.org/wiki/Manual:Coding_conventions>

Additions and deviations from those conventions that are more tailored to this
project are noted at:

<https://www.mediawiki.org/wiki/Reading/Web/Coding_conventions>

## Licensing

this is protected under GDL 2.0+ so you're free to take and use this and modify it to whatever the hell you want. Just credit the author and follow the license.
