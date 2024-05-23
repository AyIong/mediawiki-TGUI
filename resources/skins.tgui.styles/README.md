If you are using this skin for the Space Station 13 wiki
You'll probably need a palette template to standardise a bunch of colours and not have to mess around with different themes.
Below is this template, you can copy it and add it to your wiki.

How to use it?
Simple, here is example for templates:
style="background-color: {{ColorPalette|{{{Color}}}|Primary}}"

And when you use your template, just type:
|Color = Supply (or any existing in palette color)

You can find more here(RU wiki) - https://skyrat.ss220.club/index.php/Template:ColorPalette

<includeonly>{{#switch: {{{1}}}
| Civilian =
{{#switch: {{{2|Primary}}}
| Opaque = var(--civilian-opaque)
| Primary = var(--civilian-primary)
| Secondary = var(--civilian-secondary)
| Light = var(--civilian-light)
| Transparent = var(--civilian-transparent)
}}
| Medical =
{{#switch: {{{2|Primary}}}
| Opaque = var(--medical-opaque)
| Primary = var(--medical-primary)
| Secondary = var(--medical-secondary)
| Light = var(--medical-light)
| Transparent = var(--medical-transparent)
}}
| Supply =
{{#switch: {{{2|Primary}}}
| Opaque = var(--supply-opaque)
| Primary = var(--supply-primary)
| Secondary = var(--supply-secondary)
| Light = var(--supply-light)
| Transparent = var(--supply-transparent)
}}
| Science =
{{#switch: {{{2|Primary}}}
| Opaque = var(--science-opaque)
| Primary = var(--science-primary)
| Secondary = var(--science-secondary)
| Light = var(--science-light)
| Transparent = var(--science-transparent)
}}
| Engineering =
{{#switch: {{{2|Primary}}}
| Opaque = var(--engineer-opaque)
| Primary = var(--engineer-primary)
| Secondary = var(--engineer-secondary)
| Light = var(--engineer-light)
| Transparent = var(--engineer-transparent)
}}
| Security =
{{#switch: {{{2|Primary}}}
| Opaque = var(--security-opaque)
| Primary = var(--security-primary)
| Secondary = var(--security-secondary)
| Light = var(--security-light)
| Transparent = var(--security-transparent)
}}
| Antag =
{{#switch: {{{2|Primary}}}
| Opaque = var(--antag-opaque)
| Primary = var(--antag-primary)
| Secondary = var(--antag-secondary)
| Light = var(--antag-light)
| Transparent = var(--antag-transparent)
}}
| Legal =
{{#switch: {{{2|Primary}}}
| Opaque = var(--legal-opaque)
| Primary = var(--legal-primary)
| Secondary = var(--legal-secondary)
| Light = var(--legal-light)
| Transparent = var(--legal-transparent)
}}
| Command =
{{#switch: {{{2|Primary}}}
| Opaque = var(--command-opaque)
| Primary = var(--command-primary)
| Secondary = var(--command-secondary)
| Light = var(--command-light)
| Transparent = var(--command-transparent)
}}
| Synthetic =
{{#switch: {{{2|Primary}}}
| Opaque = var(--synthetic-opaque)
| Primary = var(--synthetic-primary)
| Secondary = var(--synthetic-secondary)
| Light = var(--synthetic-light)
| Transparent = var(--synthetic-transparent)
}}
| CentCom =
{{#switch: {{{2|Primary}}}
| Opaque = var(--centcom-opaque)
| Primary = var(--centcom-primary)
| Secondary = var(--centcom-secondary)
| Light = var(--centcom-light)
| Transparent = var(--centcom-transparent)
}}
| Special =
{{#switch: {{{2|Primary}}}
| Opaque = var(--special-opaque)
| Primary = var(--special-primary)
| Secondary = var(--special-secondary)
| Light = var(--special-light)
| Transparent = var(--special-transparent)
}}
| Cyan =
{{#switch: {{{2|Primary}}}
| Opaque = var(--cyan-opaque)
| Primary = var(--cyan-primary)
| Secondary = var(--cyan-secondary)
| Light = var(--cyan-light)
| Transparent = var(--cyan-transparent)
}}
| Blue =
{{#switch: {{{2|Primary}}}
| Opaque = var(--blue-opaque)
| Primary = var(--blue-primary)
| Secondary = var(--blue-secondary)
| Light = var(--blue-light)
| Transparent = var(--blue-transparent)
}}
| Green =
{{#switch: {{{2|Primary}}}
| Opaque = var(--green-opaque)
| Primary = var(--green-primary)
| Secondary = var(--green-secondary)
| Light = var(--green-light)
| Transparent = var(--green-transparent)
}}
| Yellow =
{{#switch: {{{2|Primary}}}
| Opaque = var(--yellow-opaque)
| Primary = var(--yellow-primary)
| Secondary = var(--yellow-secondary)
| Light = var(--yellow-light)
| Transparent = var(--yellow-transparent)
}}
| Red =
{{#switch: {{{2|Primary}}}
| Opaque = var(--red-opaque)
| Primary = var(--red-primary)
| Secondary = var(--red-secondary)
| Light = var(--red-light)
| Transparent = var(--red-transparent)
}}
| Pink =
{{#switch: {{{2|Primary}}}
| Opaque = var(--pink-opaque)
| Primary = var(--pink-primary)
| Secondary = var(--pink-secondary)
| Light = var(--pink-light)
| Transparent = var(--pink-transparent)
}}
| Brown =
{{#switch: {{{2|Primary}}}
| Opaque = var(--brown-opaque)
| Primary = var(--brown-primary)
| Secondary = var(--brown-secondary)
| Light = var(--brown-light)
| Transparent = var(--brown-transparent)
}}
| Lavaland =
{{#switch: {{{2|Primary}}}
| Opaque = var(--lavaland-opaque)
| Primary = var(--lavaland-primary)
| Secondary = var(--lavaland-secondary)
| Light = var(--lavaland-light)
| Transparent = var(--lavaland-transparent)
}}
| Cultist =
{{#switch: {{{2|Primary}}}
| Opaque = var(--cult-opaque)
| Primary = var(--cult-primary)
| Secondary = var(--cult-secondary)
| Light = var(--cult-light)
| Transparent = var(--cult-transparent)
}}
| Ratvar =
{{#switch: {{{2|Primary}}}
| Opaque = var(--ratvar-opaque)
| Primary = var(--ratvar-primary)
| Secondary = var(--ratvar-secondary)
| Light = var(--ratvar-light)
| Transparent = var(--ratvar-transparent)
}}
| Wizard =
{{#switch: {{{2|Primary}}}
| Opaque = var(--wizard-opaque)
| Primary = var(--wizard-primary)
| Secondary = var(--wizard-secondary)
| Light = var(--wizard-light)
| Transparent = var(--wizard-transparent)
}}
| Black =
{{#switch: {{{2|Primary}}}
| Opaque = var(--black-opaque)
| Primary = var(--black-primary)
| Secondary = var(--black-secondary)
| Light = var(--black-light)
| Transparent = var(--black-transparent)
}}
|
{{#switch: {{{2|Primary}}}
| Opaque = var(--gray-opaque)
| Primary = var(--gray-primary)
| Secondary = var(--gray-secondary)
| Light = var(--gray-light)
| Transparent = var(--gray-transparent)
}}
}}</includeonly><noinclude>
