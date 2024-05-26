# TGUI Skin
[![forthebadge](https://forthebadge.com/images/badges/built-with-love.svg)](http://forthebadge.com)
[![forthebadge](https://forthebadge.com/images/badges/designed-in-ms-paint.svg)](http://forthebadge.com)
[![forthebadge](https://forthebadge.com/images/badges/works-on-my-machine-1.svg)](http://forthebadge.com)

## About
Requires MediaWiki >= 1.39.0 < 1.40.0

Based on the MediaWiki [Vector](https://www.mediawiki.org/wiki/Skin:Vector/2022) skin, inspired by the TGUI from SS13, which was originally made for [/tg/station](https://github.com/tgstation/tgstation)

Also ported preferences from [Citizen](https://github.com/StarCitizenTools/mediawiki-skins-Citizen) skin

Made by someone not versed in php, so it just works

## Installation
Download all these files into a folder named "TGUI"

Place the TGUI folder into Mediawiki/Skins (Or, clone this repo into the skin folder, and rename cloned folder into "TGUI")

Go to MediaWiki LocalSetting.php and add this line `wfLoadSkin( 'TGUI' );` next to all the other wfLoadSkin() calls

You may need to reload Mediawiki for it to show up.

Now when users go to their Special:Preferences page, they can change their appearance setting theme to TGUI.

Also, you can make it by default `$wgDefaultSkin = "tgui";`

## Uninstall

REQUIRED: remove this line `wfLoadSkin( 'TGUI' );` from LocalSetting.php

REQUIRED: If you set $wgDefaultSkin to 'tgui' you must switch it to another skin you have installed

MediaWiki will automatically switch users using TGUI back to the default skin

You can keep the TGUI folder in Skins if you just want to disable its use for the time being without
any issues. Otherwise you can just delete it at this point.

## Configuration options
Skin has a few configurations, you can use them if your wiki is not ready to use the standard dark theme

| Name | Description | Values | Default |
| - | - | - | - |
| $wgTGUIEnablePreferences | Gives users the ability to customise the skin to their liking | `true` - enable <br> `false` - disable | `true` |
| $wgTGUIThemeDefault | The default skin theme | `auto` - automatically switches between dark and light, depending on the theme of the device <br> `dark` and `light` <br> | `auto`

## Licensing
This is protected under GDL 2.0+ so you're free to take and use this and modify it to whatever the hell you want. Just credit the author and follow the license.
